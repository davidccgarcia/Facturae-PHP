<?php

ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Facturae\Facturae;
use josemmo\Facturae\FacturaeParty;

// Creamos la factura
$fac = new Facturae();

// Asignamos el número EMP2017120003 a la factura
// Nótese que Facturae debe recibir el lote y el
// número separados
$fac->setNumber('9000000104004370', '980000000');

// Asignamos el 01/12/2017 como fecha de la factura
$fac->setIssueDate('2017-12-01');

// Asignamos fecha de inicio y fecha de fin para factura electrónica
$fac->setBillingPeriod('2018-04-13', '2028-04-13')
    ->setPrefix('PRUE')
    ->setBillingRanges(980000000, 985000000)
    ->setIdentificationCode('CO')
    ->setSoftwareProvider(900242742, '6a7bc0fe-d361-4fee-92b0-cf41e099e454')
    ->setSoftwareSecurityCode('sFABILU');

// Incluimos los datos del vendedor
$fac->setSeller(new FacturaeParty([
    "taxNumber" => "A00000000",
    "name"      => "Perico de los Palotes S.A.",
    "address"   => "C/ Falsa, 123",
    "postCode"  => "12345",
    "town"      => "Madrid",
    "province"  => "Madrid"
]));

// Incluimos los datos del comprador,
// con finos demostrativos el comprador será
// una persona física en vez de una empresa
$fac->setBuyer(new FacturaeParty([
    "isLegalEntity" => false,       // Importante!
    "taxNumber"     => "00000000A",
    "name"          => "Antonio",
    "firstSurname"  => "García",
    "lastSurname"   => "Pérez",
    "address"       => "Avda. Mayor, 7",
    "postCode"      => "54321",
    "town"          => "Madrid",
    "province"      => "Madrid"
]));

// Añadimos los productos a incluir en la factura
// En este caso, probaremos con tres lámpara por
// precio unitario de 20,14€ con 21% de IVA ya incluído
$fac->addItem("Lámpara de pie", 20.14, 3, Facturae::TAX_IVA, 21);

// Ya solo queda firmar la factura ...
$fac->sign(__DIR__ . '/../tests/persona_juridica_pruebas_vigente.p12', null, 'persona_juridica_pruebas');

// ... y exportarlo a un archivo
$fac->export(__DIR__ . "/../storage/salida.xml");
