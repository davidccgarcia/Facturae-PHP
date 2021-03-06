<?php
namespace josemmo\Facturae\Controller;

use josemmo\Facturae\Common\XmlTools;

/**
 * Allows a @link{josemmo\Facturae\Facturae} instance to be exported to XML.
 */
abstract class FacturaeExportable extends FacturaeSignable {

  /**
   * Add optional fields
   * @param  object   $item   Subject item
   * @param  string[] $fields Optional fields
   * @return string           Output XML
   */
  private function addOptionalFields($item, $fields) {
    $tools = new XmlTools();

    $res = "";
    foreach ($fields as $key=>$name) {
      if (is_int($key)) $key = $name; // Allow $item to have a different property name
      if (!empty($item[$key])) {
        $xmlTag = ucfirst($name);
        $res .= "<$xmlTag>" . $tools->escape($item[$key]) . "</$xmlTag>";
      }
    }
    return $res;
  }


  /**
   * Export
   *
   * Get Facturae XML data
   *
   * @param  string     $filePath Path to save invoice
   * @return string|int           XML data|Written file bytes
   */
  public function export($filePath=null) {
    $tools = new XmlTools();

    $xml = '<fe:Invoice xmlns:fe="'. self::$SCHEMA_NS[$this->version] .'" ' .
            'xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" ' .
            'xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" ' .
            'xmlns:clm54217="urn:un:unece:uncefact:codelist:specification:54217:2001" ' .
            'xmlns:clm66411="urn:un:unece:uncefact:codelist:specification:66411:2001" ' .
            'xmlns:clmIANAMIMEMediaType="urn:un:unece:uncefact:codelist:specification:IANAMIMEMediaType:2003" ' .
            'xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" ' .
            'xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" ' .
            'xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures" ' .
            'xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xsi:schemaLocation="http://www.dian.gov.co/contratos/facturaelectronica/v1 ../xsd/DIAN_UBL.xsd urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2 ../../ubl2/common/UnqualifiedDataTypeSchemaModule-2.0.xsd urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2 ../../ubl2/common/UBL-QualifiedDatatypes-2.0.xsd">';
    
    $totals = $this->getTotals();

    $xml .= '<ext:UBLExtensions>' .
              '<ext:UBLExtension>' . 
                '<ext:ExtensionContent>' . 
                  '<sts:DianExtensions>' .
                    '<sts:InvoiceControl>' .
                      '<sts:InvoiceAuthorization>9000000141004745</sts:InvoiceAuthorization>' . 
                      '<sts:AuthorizationPeriod>' .
                        '<cbc:StartDate>2018-04-13</cbc:StartDate>' .
                        '<cbc:EndDate>2028-04-13</cbc:EndDate>' .
                      '</sts:AuthorizationPeriod>' .
                      '<sts:AuthorizedInvoices>' .
                        '<sts:Prefix>PRUE</sts:Prefix>' .
                        '<sts:From>980000000</sts:From>' .
                        '<sts:To>985000000</sts:To>' .
                      '</sts:AuthorizedInvoices>' . 
                    '</sts:InvoiceControl>' .
                    '<sts:InvoiceSource>' . 
                      '<cbc:IdentificationCode listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe" listSchemeURI="urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.0">CO</cbc:IdentificationCode>' .
                    '</sts:InvoiceSource>' .
                    '<sts:SoftwareProvider>' .
                      '<sts:ProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)">' .
                        '900373115' .
                      '</sts:ProviderID>' .
                      '<sts:SoftwareID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)">' .
                        '0d2e2883-eb8d-4237-87fe-28aeb71e961e' .
                      '</sts:SoftwareID>' .
                    '</sts:SoftwareProvider>' . 
                    '<sts:SoftwareSecurityCode schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)">' .
                      'bdaa51c9953e08dcc8f398961f7cd0717cd5fbea356e937660aa1a8abbe31f4c9b4eb5cf8682eaca4c8523953253dcce' .
                    '</sts:SoftwareSecurityCode>' .
                  '</sts:DianExtensions>' .
                '</ext:ExtensionContent>' .
              '</ext:UBLExtension>' . 
              '<ext:UBLExtension>' .
                '<ext:ExtensionContent>';
    
    // Close invoice and document
    $xml .= '</ext:ExtensionContent></ext:UBLExtension></ext:UBLExtensions>';

    $xml = $this->injectSignature($xml);

    $xml .= '<cbc:UBLVersionID>UBL 2.0</cbc:UBLVersionID>' .
            '<cbc:ProfileID>DIAN 1.0</cbc:ProfileID>' .
            '<cbc:ID>PRUE980007161</cbc:ID>' .
            '<cbc:UUID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)">a3d6c86a71cbc066aaa19fd363c0fe4b5778d4a0</cbc:UUID>' . 
            '<cbc:IssueDate>2016-07-12</cbc:IssueDate>' .
            '<cbc:IssueTime>00:31:40</cbc:IssueTime>' .
            '<cbc:InvoiceTypeCode listAgencyID="195" listAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)" listSchemeURI="http://www.dian.gov.co/contratos/facturaelectronica/v1/InvoiceType">1</cbc:InvoiceTypeCode>' . 
            '<cbc:Note>Set de pruebas</cbc:Note>' .
            '<cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>' .
            '<fe:AccountingSupplierParty>' .
              '<cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>' .
                '<fe:Party>' .
                  '<cac:PartyIdentification>' .
                    '<cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)" schemeID="31">900373115</cbc:ID>' .
                  '</cac:PartyIdentification>' .
                  '<cac:PartyName>' . 
                    '<cbc:Name>PJ - 900373115 - Adquiriente FE</cbc:Name>' .
                  '</cac:PartyName>' .
                  '<fe:PhysicalLocation>' .
                    '<fe:Address>' .
                      '<cbc:Department>Distrito Capital</cbc:Department>' . 
                      '<cbc:CitySubdivisionName>Centro</cbc:CitySubdivisionName>' .
                      '<cbc:CityName>Bogotá</cbc:CityName>' . 
                      '<cac:AddressLine>' . 
                        '<cbc:Line>  carrera 8 Nº 6C - 78</cbc:Line>' . 
                      '</cac:AddressLine>' .
                      '<cac:Country>' .
                        '<cbc:IdentificationCode>CO</cbc:IdentificationCode>' .
                      '</cac:Country>' . 
                    '</fe:Address>' .
                  '</fe:PhysicalLocation>' . 
                  '<fe:PartyTaxScheme>' .
                    '<cbc:TaxLevelCode>0</cbc:TaxLevelCode>' .
                    '<cac:TaxScheme/>' .
                  '</fe:PartyTaxScheme>' . 
                  '<fe:PartyLegalEntity>' .
                    '<cbc:RegistrationName>PJ - 900373115</cbc:RegistrationName>' .
                  '</fe:PartyLegalEntity>' . 
                '</fe:Party>'.
              '</fe:AccountingSupplierParty>' . 
              '<fe:AccountingCustomerParty>' .
                '<cbc:AdditionalAccountID>2</cbc:AdditionalAccountID>' . 
                '<fe:Party>' . 
                  '<cac:PartyIdentification>' .
                    '<cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y Aduanas Nacionales)" schemeID="22">11333000</cbc:ID>' .
                  '</cac:PartyIdentification>' .
                  '<fe:PhysicalLocation>' .
                    '<fe:Address>' .
                      '<cbc:Department>Valle del Cauca</cbc:Department>' .
                      '<cbc:CitySubdivisionName>Centro</cbc:CitySubdivisionName>' .
                      '<cbc:CityName>Toribio</cbc:CityName>' .
                      '<cac:AddressLine>' .
                          '<cbc:Line>  carrera 8 Nº 6C - 46</cbc:Line>' .
                      '</cac:AddressLine>' .
                      '<cac:Country>' .
                        '<cbc:IdentificationCode>CO</cbc:IdentificationCode>' .
                      '</cac:Country>' .
                    '</fe:Address>' .
                  '</fe:PhysicalLocation>' .
                  '<fe:PartyTaxScheme>' .
                    '<cbc:TaxLevelCode>0</cbc:TaxLevelCode>' .
                    '<cac:TaxScheme/>' .
                  '</fe:PartyTaxScheme>' .
                  '<fe:Person>' .
                    '<cbc:FirstName>Primer-N</cbc:FirstName>' .
                    '<cbc:FamilyName>Apellido-11333000</cbc:FamilyName>' .
                    '<cbc:MiddleName>Segundo-N</cbc:MiddleName>' .
                  '</fe:Person>' .
                '</fe:Party>' . 
              '</fe:AccountingCustomerParty>' .
              '<fe:TaxTotal>' .
                '<cbc:TaxAmount currencyID="COP">109625.61</cbc:TaxAmount>' .
                '<cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>' .
                '<fe:TaxSubtotal>' .
                  '<cbc:TaxableAmount currencyID="COP">1134840.69</cbc:TaxableAmount>' .
                  '<cbc:TaxAmount currencyID="COP">109625.61</cbc:TaxAmount>' .
                  '<cbc:Percent>9.66</cbc:Percent>' .
                  '<cac:TaxCategory>' .
                    '<cac:TaxScheme>' .
                      '<cbc:ID>03</cbc:ID>' .
                    '</cac:TaxScheme>' .
                  '</cac:TaxCategory>' .
                '</fe:TaxSubtotal>' .
              '</fe:TaxTotal>' .
              '<fe:TaxTotal>' .
                '<cbc:TaxAmount currencyID="COP">46982.4</cbc:TaxAmount>' .
                '<cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>' .
                '<fe:TaxSubtotal>' .
                    '<cbc:TaxableAmount currencyID="COP">1134840.69</cbc:TaxableAmount>' .
                    '<cbc:TaxAmount currencyID="COP">46982.4</cbc:TaxAmount>' .
                    '<cbc:Percent>4.14</cbc:Percent>' .
                    '<cac:TaxCategory>' .
                      '<cac:TaxScheme>' .
                        '<cbc:ID>02</cbc:ID>' .
                      '</cac:TaxScheme>' .
                    '</cac:TaxCategory>' .
                '</fe:TaxSubtotal>' .
              '</fe:TaxTotal>' .
              '<fe:LegalMonetaryTotal>' .
                '<cbc:LineExtensionAmount currencyID="COP">1134840.69</cbc:LineExtensionAmount>' .
                '<cbc:TaxExclusiveAmount currencyID="COP">156608.01</cbc:TaxExclusiveAmount>' .
                '<cbc:PayableAmount currencyID="COP">1291448.7</cbc:PayableAmount>' .
              '</fe:LegalMonetaryTotal>' .
              '<fe:InvoiceLine>' .
                '<cbc:ID>1</cbc:ID>' .
                '<cbc:InvoicedQuantity>765</cbc:InvoicedQuantity>' .
                '<cbc:LineExtensionAmount currencyID="COP">1134840.697170767</cbc:LineExtensionAmount>' .
                '<fe:Item>' .
                  '<cbc:Description>Línea-1 PRUE980007161 f-s0001_900373115_0d2e2_R9000000500017960-PRUE-A_cufe</cbc:Description>' .
                '</fe:Item>' .
                '<fe:Price>' .
                  '<cbc:PriceAmount currencyID="COP">1483.4518917264927</cbc:PriceAmount>' .
                '</fe:Price>' .
              '</fe:InvoiceLine>' .
            '</fe:Invoice>';

    // Prepend content type
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
    // Save document
    if (!is_null($filePath)) return file_put_contents($filePath, $xml);
    return $xml;
  }

}
