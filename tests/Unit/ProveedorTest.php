<?php

use App\Models\Proveedor;

it('uses the proveedores table', function () {
    expect((new Proveedor)->getTable())->toBe('proveedores');
});
