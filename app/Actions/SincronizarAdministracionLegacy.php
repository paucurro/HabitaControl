<?php

namespace App\Actions;

use App\Actions\Diario\CalcularFechasAproximadas;
use App\Models\Administracion;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class SincronizarAdministracionLegacy
{
    public function __construct(private CalcularFechasAproximadas $calcularFechas) {}

    /** @return array<string, int> */
    public function handle(Administracion $administracion): array
    {
        $source = $this->sourceDatabase($administracion);
        $this->assertSourceIsReachable($administracion, $source);
        $connection = DB::connection();
        $before = $this->counts($connection, $source);
        $originalSqlMode = (string) $connection->selectOne('SELECT @@SESSION.sql_mode AS value')->value;

        try {
            $connection->statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

            foreach ($this->statements($source, $administracion->id, $administracion->propietario_user_id) as $statement) {
                $connection->statement($statement);
            }

            $datesFixed = $connection->affectingStatement(<<<SQL
                UPDATE diario_apuntes target
                JOIN `{$source}`.`diario` source
                  ON target.legacy_source = ? AND target.legacy_id = source.ind_diario
                SET target.fecha = DATE(FROM_UNIXTIME(source.fecha_diario)), target.fecha_calculada = 0, target.updated_at = NOW()
                WHERE source.fecha_diario > 0
                  AND (target.fecha IS NULL OR target.fecha <> DATE(FROM_UNIXTIME(source.fecha_diario)))
                SQL, [$source]);
        } finally {
            $connection->statement('SET SESSION sql_mode = ?', [$originalSqlMode]);
        }

        $after = $this->counts($connection, $source);
        $datesCalculated = $this->calcularFechas->handle($source);

        return [
            'comunidades_nuevas' => $after['comunidades'] - $before['comunidades'],
            'partes_nuevas' => $after['partes'] - $before['partes'],
            'propietarios_nuevos' => $after['propietarios'] - $before['propietarios'],
            'apuntes_nuevos' => $after['diario'] - $before['diario'],
            'liquidaciones_nuevas' => $after['liquidaciones'] - $before['liquidaciones'],
            'fechas_corregidas' => $datesFixed,
            'fechas_calculadas' => $datesCalculated,
        ];
    }

    private function sourceDatabase(Administracion $administracion): string
    {
        $source = (string) $administracion->db_origen;

        if ($source === '' || preg_match('/\A[a-zA-Z0-9_]+\z/', $source) !== 1) {
            throw new InvalidArgumentException('La base de datos de origen no es válida.');
        }

        return $source;
    }

    private function assertSourceIsReachable(Administracion $administracion, string $source): void
    {
        if ($administracion->db_usuario !== null && $administracion->db_usuario !== '') {
            config(['database.connections.legacy_sync' => [
                'driver' => 'mysql',
                'host' => $administracion->db_host ?: config('database.connections.mysql.host'),
                'port' => $administracion->db_port ?: config('database.connections.mysql.port'),
                'database' => $source,
                'username' => $administracion->db_usuario,
                'password' => $administracion->db_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ]]);
            DB::purge('legacy_sync');
            DB::connection('legacy_sync')->table('comunidades')->limit(1)->get();
            DB::disconnect('legacy_sync');
        }

        try {
            DB::connection()->selectOne("SELECT 1 FROM `{$source}`.`comunidades` LIMIT 1");
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                'La conexión principal necesita permiso SELECT sobre la base de origen para realizar la sincronización.',
                previous: $exception,
            );
        }
    }

    /** @return array{comunidades: int, partes: int, propietarios: int, diario: int, liquidaciones: int} */
    private function counts(ConnectionInterface $connection, string $source): array
    {
        return [
            'comunidades' => (int) $connection->table('comunidades')->where('legacy_source', $source)->count(),
            'partes' => (int) $connection->table('partes')->where('legacy_source', $source)->count(),
            'propietarios' => (int) $connection->table('propietarios')->where('legacy_source', $source)->count(),
            'diario' => (int) $connection->table('diario_apuntes')->where('legacy_source', $source)->count(),
            'liquidaciones' => (int) $connection->table('liquidaciones')->where('legacy_source', $source)->count(),
        ];
    }

    /** @return list<string> */
    private function statements(string $source, int $administracionId, int $userId): array
    {
        $src = "`{$source}`";

        return [
            "INSERT INTO comunidades (legacy_id,legacy_source,administracion_id,user_id,codigo,nif,nombre,direccion,codigo_postal,poblacion,provincia,pais,fondo_reserva,created_at,updated_at,deleted_at) SELECT s.ind_comunidades,'{$source}',{$administracionId},{$userId},LEFT(s.codigo_comunidades,20),LEFT(s.nif_comunidades,20),LEFT(COALESCE(NULLIF(s.descripcion_comunidades,''),CONCAT('Comunidad ',s.ind_comunidades)),200),LEFT(s.direccion_comunidades,200),LEFT(s.c_postal_comunidades,10),LEFT(s.poblacion_comunidades,100),LEFT(s.provincia_comunidades,100),'España',COALESCE(NULLIF(s.fondo_reserva_comunidades,''),0),NOW(),NOW(),IF(s.borrada_comunidades<>0,NOW(),NULL) FROM {$src}.comunidades s WHERE NOT EXISTS(SELECT 1 FROM comunidades t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_comunidades)",
            "INSERT INTO bancos (legacy_id,legacy_source,comunidad_id,nombre,direccion,codigo_postal,poblacion,provincia,telefonos,iban,bic,codigo_interno,created_at,updated_at) SELECT s.ind_bancos,'{$source}',c.id,LEFT(s.nombre_bancos,200),LEFT(s.direccion_bancos,200),LEFT(s.cp_bancos,10),LEFT(s.poblacion_bancos,100),LEFT(s.provincia_bancos,100),LEFT(s.telefonos_bancos,200),LEFT(REPLACE(s.iban_bancos,' ',''),34),LEFT(s.bic_bancos,11),LEFT(s.codigo_bancos,20),NOW(),NOW() FROM {$src}.bancos s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades WHERE NOT EXISTS(SELECT 1 FROM bancos t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_bancos)",
            "INSERT INTO tipos_deposito (legacy_id,legacy_source,comunidad_id,nombre,importe,created_at,updated_at) SELECT s.ind_tipos_depositos,'{$source}',c.id,LEFT(COALESCE(NULLIF(s.descripcion_tipos_depositos,''),CONCAT('Depósito ',s.ind_tipos_depositos)),200),COALESCE(NULLIF(s.importe_tipos_depositos,''),0),NOW(),NOW() FROM {$src}.tipos_depositos s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades WHERE NOT EXISTS(SELECT 1 FROM tipos_deposito t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_tipos_depositos)",
            "INSERT INTO partes (legacy_id,legacy_source,comunidad_id,tipo_deposito_id,codigo,descripcion,deposito,coeficiente_general,observaciones,created_at,updated_at,deleted_at) SELECT s.ind_partes,'{$source}',c.id,td.id,IF(EXISTS(SELECT 1 FROM partes p2 WHERE p2.comunidad_id=c.id AND p2.codigo=LEFT(s.codigo_partes,20)),CONCAT(LEFT(s.codigo_partes,11),' #',s.ind_partes),LEFT(COALESCE(NULLIF(s.codigo_partes,''),CONCAT('PARTE-',s.ind_partes)),20)),LEFT(s.desc_partes,200),COALESCE(NULLIF(s.deposito_partes,''),0),COALESCE(NULLIF(s.coeficiente_partes,''),0),s.observaciones_partes,NOW(),NOW(),IF(s.borrada_partes<>0,NOW(),NULL) FROM {$src}.partes s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades LEFT JOIN tipos_deposito td ON td.legacy_source='{$source}' AND td.legacy_id=s.id_tipos_depositos WHERE NOT EXISTS(SELECT 1 FROM partes t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_partes)",
            "INSERT INTO propietarios (legacy_id,legacy_source,administracion_id,tipo,nombre,conyuge,nif,direccion,codigo_postal,poblacion,provincia,pais,telefono,telefono_trabajo,movil,emails,iban,bic,domiciliar_recibos,enviar_email,observaciones,created_at,updated_at) SELECT s.ind_propietarios,'{$source}',{$administracionId},LEFT(s.tipo_propietarios,30),LEFT(COALESCE(NULLIF(s.nombre_propietarios,''),CONCAT('Propietario ',s.ind_propietarios)),200),LEFT(s.nombre_conyuge_propietarios,100),LEFT(s.dni_propietarios,20),LEFT(s.direccion_propietarios,200),LEFT(s.cp_propietarios,10),LEFT(s.poblacion_propietarios,100),LEFT(s.provincia_propietarios,100),LEFT(s.pais_propietarios,100),LEFT(s.telefono_propietarios,30),LEFT(s.telftrabajo_propietarios,30),LEFT(s.movil_propietarios,30),s.email_propietarios,LEFT(REPLACE(s.iban_propietarios,' ',''),34),LEFT(s.bic_propietarios,11),COALESCE(s.recibo_propietarios,0)<>0,COALESCE(s.send_email_propietarios,0)<>0,s.observaciones_propietarios,NOW(),NOW() FROM {$src}.propietarios_1 s WHERE NOT EXISTS(SELECT 1 FROM propietarios t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_propietarios)",
            "INSERT INTO parte_propietario (legacy_id,legacy_source,parte_id,propietario_id,imprimir_etiqueta,imprimir_liquidacion,created_at,updated_at,deleted_at) SELECT s.ind_partes_prop,'{$source}',p.id,o.id,COALESCE(s.imp_etiqueta_partes_prop,0)<>0,COALESCE(s.imp_liquidacion_partes_prop,0)<>0,NOW(),NOW(),IF(s.borrada_partes_prop<>0,NOW(),NULL) FROM {$src}.partes_prop s JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_partes JOIN propietarios o ON o.legacy_source='{$source}' AND o.legacy_id=s.id_propietarios WHERE NOT EXISTS(SELECT 1 FROM parte_propietario t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_partes_prop)",
            "INSERT INTO tipos_gasto (legacy_id,legacy_source,comunidad_id,codigo,descripcion,notas,created_at,updated_at) SELECT s.ind_tipo_gastos,'{$source}',c.id,LEFT(COALESCE(NULLIF(s.codigo_tipo_gastos,''),CONCAT('G-',s.ind_tipo_gastos)),20),LEFT(COALESCE(NULLIF(s.descripcion_tipo_gastos,''),CONCAT('Gasto ',s.ind_tipo_gastos)),200),s.notas_tipo_gastos,NOW(),NOW() FROM {$src}.tipo_gastos s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades WHERE NOT EXISTS(SELECT 1 FROM tipos_gasto t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_tipo_gastos)",
            "INSERT IGNORE INTO coeficientes (legacy_id,legacy_source,comunidad_id,parte_id,tipo_gasto_id,porcentaje,created_at,updated_at) SELECT s.ind_coeficientes,'{$source}',p.comunidad_id,p.id,tg.id,COALESCE(NULLIF(s.porcentaje_coeficientes,''),0),NOW(),NOW() FROM {$src}.coeficientes s JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_partes JOIN tipos_gasto tg ON tg.legacy_source='{$source}' AND tg.legacy_id=s.id_tipo_gasto WHERE NOT EXISTS(SELECT 1 FROM coeficientes t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_coeficientes)",
            "INSERT INTO diario_apuntes (legacy_id,legacy_source,comunidad_id,tipo_gasto_id,banco_id,parte_id,fecha,numero_documento,descripcion,debe,haber,base_imponible,porcentaje_iva,created_at,updated_at) SELECT s.ind_diario,'{$source}',c.id,tg.id,b.id,p.id,DATE(FROM_UNIXTIME(s.fecha_diario)),LEFT(s.id_num_documento,50),LEFT(COALESCE(NULLIF(s.descripcion_diario,''),'Apunte'),200),COALESCE(NULLIF(s.importe_d_diario,''),0),COALESCE(NULLIF(s.importe_h_diario,''),0),NULLIF(s.base_diario,''),NULLIF(s.iva_diario,''),NOW(),NOW() FROM {$src}.diario s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades LEFT JOIN tipos_gasto tg ON tg.legacy_source='{$source}' AND tg.legacy_id=s.id_tipogasto LEFT JOIN bancos b ON b.legacy_source='{$source}' AND b.legacy_id=s.id_banco LEFT JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_partes WHERE s.fecha_diario>0 AND NOT EXISTS(SELECT 1 FROM diario_apuntes t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_diario)",
            "INSERT INTO liquidaciones (legacy_id,legacy_source,comunidad_id,desde,hasta,fecha_emision,descripcion,estado,completada_at,texto,penalizacion,fondo_reserva,imprimir_nombres,created_at,updated_at) SELECT s.ind_liquid_cabecera,'{$source}',c.id,COALESCE(NULLIF(s.desde_fecha_liquid_cabecera,'0000-00-00'),'1970-01-01'),COALESCE(NULLIF(s.hasta_fecha_liquid_cabecera,'0000-00-00'),'1970-01-01'),COALESCE(NULLIF(s.fecha_liquid_cabecera,'0000-00-00'),'1970-01-01'),CONCAT('Liquidación ',s.ind_liquid_cabecera),IF(COALESCE(s.completada_liquid_cabecera,0)<>0,'completada','borrador'),IF(COALESCE(s.completada_liquid_cabecera,0)<>0,NOW(),NULL),s.descrip_liquidacion_liquid_cabecera,COALESCE(NULLIF(s.penalizacion_liquid_cabecera,''),0),COALESCE(NULLIF(s.fondo_reserva_liquid_cabecera,''),0),COALESCE(s.imprimir_nombres_liquid_cabecera,0)<>0,NOW(),NOW() FROM {$src}.liquid_cabecera s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades WHERE NOT EXISTS(SELECT 1 FROM liquidaciones t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_liquid_cabecera)",
            "INSERT INTO liquidacion_lineas (legacy_id,legacy_source,liquidacion_id,diario_apunte_id,comunidad_id,parte_id,banco_id,tipo_gasto_id,operacion,fecha,numero_documento,descripcion,importe,coeficiente,tipo_gasto_codigo,tipo_gasto_descripcion,created_at,updated_at) SELECT s.ind_liquid_lines,'{$source}',l.id,d.id,c.id,p.id,b.id,tg.id,LEFT(s.operacion_liquid_lines,30),COALESCE(NULLIF(s.fecha_apunte_liquid_lines,'0000-00-00'),'1970-01-01'),LEFT(s.id_num_documento,50),LEFT(COALESCE(NULLIF(s.descripcion_liquid_lines,''),'Línea'),200),COALESCE(NULLIF(s.importe_liquid_lines,''),0),COALESCE(NULLIF(s.coeficiente_liquid_lines,''),0),LEFT(s.tipo_gasto_liquid_lines,20),LEFT(s.tipo_gasto_descr_liquid_lines,200),NOW(),NOW() FROM {$src}.liquid_lines s JOIN liquidaciones l ON l.legacy_source='{$source}' AND l.legacy_id=s.id_liquid_cabecera JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades LEFT JOIN diario_apuntes d ON d.legacy_source='{$source}' AND d.legacy_id=s.id_diario LEFT JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_partes LEFT JOIN bancos b ON b.legacy_source='{$source}' AND b.legacy_id=s.id_banco LEFT JOIN tipos_gasto tg ON tg.legacy_source='{$source}' AND tg.legacy_id=s.id_tipo_gasto WHERE NOT EXISTS(SELECT 1 FROM liquidacion_lineas t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_liquid_lines)",
            "INSERT IGNORE INTO liquidacion_partes (legacy_id,legacy_source,liquidacion_id,comunidad_id,parte_id,codigo,descripcion,deposito,coeficiente,created_at,updated_at) SELECT s.ind_liquid_partes,'{$source}',l.id,c.id,p.id,LEFT(s.codigo_liquid_partes,20),LEFT(s.desc_partes_liquid_partes,250),COALESCE(NULLIF(s.deposito_liquid_partes,''),0),COALESCE(NULLIF(s.coeficiente_liquid_partes,''),0),NOW(),NOW() FROM {$src}.liquid_partes s JOIN liquidaciones l ON l.legacy_source='{$source}' AND l.legacy_id=s.id_liquid_cabecera JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_partes WHERE NOT EXISTS(SELECT 1 FROM liquidacion_partes t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_liquid_partes)",
            "INSERT IGNORE INTO liquidacion_propietarios (legacy_id,legacy_source,liquidacion_id,comunidad_id,propietario_id,datos_propietario,enviar_email,created_at,updated_at) SELECT s.ind_liquid_prop,'{$source}',l.id,c.id,o.id,JSON_OBJECT('nombre',s.nombre_prop_liquid_prop,'nif',s.dni_prop_liquid_prop,'email',s.email_prop_liquid_prop),COALESCE(s.send_email_liquid_prop,0)<>0,NOW(),NOW() FROM {$src}.liquid_prop s JOIN liquidaciones l ON l.legacy_source='{$source}' AND l.legacy_id=s.id_liquid_cabecera JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades JOIN propietarios o ON o.legacy_source='{$source}' AND o.legacy_id=s.id_propietarios WHERE NOT EXISTS(SELECT 1 FROM liquidacion_propietarios t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_liquid_prop)",
            "INSERT IGNORE INTO liquidacion_parte_propietario (legacy_id,legacy_source,liquidacion_id,parte_id,propietario_id,imprimir_etiqueta,created_at,updated_at) SELECT s.ind_liquid_parte_prop,'{$source}',l.id,p.id,o.id,COALESCE(s.imp_etiqueta_liquid_parte_prop,0)<>0,NOW(),NOW() FROM {$src}.liquid_parte_prop s JOIN liquidaciones l ON l.legacy_source='{$source}' AND l.legacy_id=s.id_liquid_cabecera JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_partes JOIN propietarios o ON o.legacy_source='{$source}' AND o.legacy_id=s.id_propietarios WHERE NOT EXISTS(SELECT 1 FROM liquidacion_parte_propietario t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_liquid_parte_prop)",
            "INSERT INTO siniestros (legacy_id,legacy_source,comunidad_id,fecha,descripcion,aseguradora,poliza,contacto,telefono,estado,cerrado_at,created_at,updated_at) SELECT s.ind_siniestros,'{$source}',c.id,COALESCE(NULLIF(s.fecha_siniestros,'0000-00-00'),'1970-01-01'),s.descripcion_siniestros,LEFT(s.cia_seguro_siniestros,100),LEFT(s.poliza_siniestros,60),LEFT(s.contacto_seguro_siniestros,100),LEFT(s.telefono_seguro_siniestros,30),IF(COALESCE(s.hecho_siniestros,0)<>0,'cerrado','abierto'),IF(COALESCE(s.hecho_siniestros,0)<>0,NOW(),NULL),NOW(),NOW() FROM {$src}.siniestros s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades WHERE NOT EXISTS(SELECT 1 FROM siniestros t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_siniestros)",
            "INSERT INTO incidencias (legacy_id,legacy_source,comunidad_id,parte_id,asignada_a_user_id,fecha,titulo,descripcion,prioridad,estado,resuelta_at,created_at,updated_at) SELECT s.ind_incidencias,'{$source}',c.id,p.id,{$userId},COALESCE(NULLIF(s.fecha_incidencias,'0000-00-00'),'1970-01-01'),LEFT(s.descripcion_incidencias,200),COALESCE(NULLIF(s.descripcion_incidencias,''),'Incidencia'),'normal',IF(COALESCE(s.hecho_incidencias,0)<>0,'resuelta','abierta'),IF(COALESCE(s.hecho_incidencias,0)<>0,NOW(),NULL),NOW(),NOW() FROM {$src}.incidencias s JOIN comunidades c ON c.legacy_source='{$source}' AND c.legacy_id=s.id_comunidades LEFT JOIN partes p ON p.legacy_source='{$source}' AND p.legacy_id=s.id_parte WHERE NOT EXISTS(SELECT 1 FROM incidencias t WHERE t.legacy_source='{$source}' AND t.legacy_id=s.ind_incidencias)",
        ];
    }
}
