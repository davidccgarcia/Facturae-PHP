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
                '<ext:ExtensionContent>' .
                '';
    
    // Close invoice and document
    $xml .= '</ext:UBLExtensions></ext:ExtensionContent></fe:Invoice>';
    foreach ($this->extensions as $ext) $xml = $ext->__onBeforeSign($xml);

    $xml = $this->injectSignature($xml);
    // foreach ($this->extensions as $ext) $xml = $ext->__onAfterSign($xml);
    foreach ($this->extensions as $ext) $xml = $ext->__onAfterSign($xml);

    echo "<pre>"; print_r($this->header); echo "</pre>";
    echo htmlentities($xml); exit;

    // Add parties
    $xml .= '<Parties>' .
              '<SellerParty>' . $this->parties['seller']->getXML($this->version) . '</SellerParty>' .
              '<BuyerParty>' . $this->parties['buyer']->getXML($this->version) . '</BuyerParty>' .
            '</Parties>';

    // Add invoice data
    $xml .= '<Invoices><Invoice>';
    $xml .= '<InvoiceHeader>' .
        '<InvoiceNumber>' . $this->header['number'] . '</InvoiceNumber>' .
        '<InvoiceSeriesCode>' . $this->header['serie'] . '</InvoiceSeriesCode>' .
        '<InvoiceDocumentType>FC</InvoiceDocumentType>' .
        '<InvoiceClass>OO</InvoiceClass>' .
      '</InvoiceHeader>';
    $xml .= '<InvoiceIssueData>';
    $xml .= '<IssueDate>' . date('Y-m-d', $this->header['issueDate']) . '</IssueDate>';
    if (!is_null($this->header['startDate'])) {
      $xml .= '<InvoicingPeriod>' .
          '<StartDate>' . date('Y-m-d', $this->header['startDate']) . '</StartDate>' .
          '<EndDate>' . date('Y-m-d', $this->header['endDate']) . '</EndDate>' .
        '</InvoicingPeriod>';
    }
    $xml .= '<InvoiceCurrencyCode>' . $this->currency . '</InvoiceCurrencyCode>';
    $xml .= '<TaxCurrencyCode>' . $this->currency . '</TaxCurrencyCode>';
    $xml .= '<LanguageName>' . $this->language . '</LanguageName>';
    $xml .= $this->addOptionalFields($this->header, [
      "description" => "InvoiceDescription",
      "receiverTransactionReference",
      "fileReference",
      "receiverContractReference"
    ]);
    $xml .= '</InvoiceIssueData>';

    // Add invoice taxes
    foreach (["taxesOutputs", "taxesWithheld"] as $taxesGroup) {
      if (count($totals[$taxesGroup]) == 0) continue;
      $xmlTag = ucfirst($taxesGroup); // Just capitalize variable name
      $xml .= "<$xmlTag>";
      foreach ($totals[$taxesGroup] as $type=>$taxRows) {
        foreach ($taxRows as $rate=>$tax) {
          $xml .= '<Tax>' .
                    '<TaxTypeCode>' . $type . '</TaxTypeCode>' .
                    '<TaxRate>' . $this->pad($rate) . '</TaxRate>' .
                    '<TaxableBase>' .
                      '<TotalAmount>' . $this->pad($tax['base']) . '</TotalAmount>' .
                    '</TaxableBase>' .
                    '<TaxAmount>' .
                      '<TotalAmount>' . $this->pad($tax['amount']) . '</TotalAmount>' .
                    '</TaxAmount>' .
                  '</Tax>';
        }
      }
      $xml .= "</$xmlTag>";
    }

    // Add invoice totals
    $xml .= '<InvoiceTotals>' .
              '<TotalGrossAmount>' . $this->pad($totals['grossAmount']) . '</TotalGrossAmount>' .
              '<TotalGeneralDiscounts>0.00</TotalGeneralDiscounts>' .
              '<TotalGeneralSurcharges>0.00</TotalGeneralSurcharges>' .
              '<TotalGrossAmountBeforeTaxes>' . $this->pad($totals['grossAmountBeforeTaxes']) . '</TotalGrossAmountBeforeTaxes>' .
              '<TotalTaxOutputs>' . $this->pad($totals['totalTaxesOutputs']) . '</TotalTaxOutputs>' .
              '<TotalTaxesWithheld>' . $this->pad($totals['totalTaxesWithheld']) . '</TotalTaxesWithheld>' .
              '<InvoiceTotal>' . $this->pad($totals['invoiceAmount']) . '</InvoiceTotal>' .
              '<TotalOutstandingAmount>' . $this->pad($totals['invoiceAmount']) . '</TotalOutstandingAmount>' .
              '<TotalExecutableAmount>' . $this->pad($totals['invoiceAmount']) . '</TotalExecutableAmount>' .
            '</InvoiceTotals>';

    // Add invoice items
    $xml .= '<Items>';
    foreach ($this->items as $itemObj) {
      $item = $itemObj->getData();
      $xml .= '<InvoiceLine>';

      // Add optional fields
      $xml .= $this->addOptionalFields($item, [
        "issuerContractReference", "issuerContractDate",
        "issuerTransactionReference", "issuerTransactionDate",
        "receiverContractReference", "receiverContractDate",
        "receiverTransactionReference", "receiverTransactionDate",
        "fileReference", "fileDate", "sequenceNumber"
      ]);

      // Add required fields
      $xml .= '<ItemDescription>' . $tools->escape($item['name']) . '</ItemDescription>' .
        '<Quantity>' . $this->pad($item['quantity']) . '</Quantity>' .
        '<UnitOfMeasure>' . $item['unitOfMeasure'] . '</UnitOfMeasure>' .
        '<UnitPriceWithoutTax>' . $this->pad($item['unitPriceWithoutTax'], 'UnitPriceWithoutTax') . '</UnitPriceWithoutTax>' .
        '<TotalCost>' . $this->pad($item['totalAmountWithoutTax'], 'TotalCost') . '</TotalCost>' .
        '<GrossAmount>' . $this->pad($item['grossAmount'], 'GrossAmount') . '</GrossAmount>';

      // Add item taxes
      // NOTE: As you can see here, taxesWithheld is before taxesOutputs.
      // This is intentional, as most official administrations would mark the
      // invoice as invalid XML if the order is incorrect.
      foreach (["taxesWithheld", "taxesOutputs"] as $taxesGroup) {
        if (count($item[$taxesGroup]) == 0) continue;
        $xmlTag = ucfirst($taxesGroup); // Just capitalize variable name
        $xml .= "<$xmlTag>";
        foreach ($item[$taxesGroup] as $type=>$tax) {
          $xml .= '<Tax>' .
                    '<TaxTypeCode>' . $type . '</TaxTypeCode>' .
                    '<TaxRate>' . $this->pad($tax['rate']) . '</TaxRate>' .
                    '<TaxableBase>' .
                      '<TotalAmount>' . $this->pad($item['totalAmountWithoutTax']) . '</TotalAmount>' .
                    '</TaxableBase>' .
                    '<TaxAmount>' .
                      '<TotalAmount>' . $this->pad($tax['amount']) . '</TotalAmount>' .
                    '</TaxAmount>' .
                  '</Tax>';
        }
        $xml .= "</$xmlTag>";
      }

      // Add more optional fields
      $xml .= $this->addOptionalFields($item, [
        "description" => "AdditionalLineItemInformation",
        "articleCode"
      ]);

      // Close invoice line
      $xml .= '</InvoiceLine>';
    }
    $xml .= '</Items>';

    // Add payment details
    if (!is_null($this->header['paymentMethod'])) {
      $dueDate = is_null($this->header['dueDate']) ?
        $this->header['issueDate'] :
        $this->header['dueDate'];
      $xml .= '<PaymentDetails>' .
                '<Installment>' .
                  '<InstallmentDueDate>' . date('Y-m-d', $dueDate) . '</InstallmentDueDate>' .
                  '<InstallmentAmount>' . $this->pad($totals['invoiceAmount']) . '</InstallmentAmount>' .
                  '<PaymentMeans>' . $this->header['paymentMethod'] . '</PaymentMeans>';
      if ($this->header['paymentMethod'] == self::PAYMENT_TRANSFER) {
        $xml .=   '<AccountToBeCredited>' .
                    '<IBAN>' . $this->header['paymentIBAN'] . '</IBAN>' .
                  '</AccountToBeCredited>';
      }
      $xml .=   '</Installment>' .
              '</PaymentDetails>';
    }

    // Add legal literals
    if (count($this->legalLiterals) > 0) {
      $xml .= '<LegalLiterals>';
      foreach ($this->legalLiterals as $reference) {
        $xml .= '<LegalReference>' . $tools->escape($reference) . '</LegalReference>';
      }
      $xml .= '</LegalLiterals>';
    }

    // Add additional data
    $extensionsXML = array();
    foreach ($this->extensions as $ext) {
      $extXML = $ext->__getAdditionalData();
      if (!empty($extXML)) $extensionsXML[] = $extXML;
    }
    if (count($extensionsXML) > 0) {
      $xml .= '<AdditionalData><Extensions>';
      $xml .= implode("", $extensionsXML);
      $xml .= '</Extensions></AdditionalData>';
    }

    // Close invoice and document
    $xml .= '</Invoice></Invoices></fe:Facturae>';
    foreach ($this->extensions as $ext) $xml = $ext->__onBeforeSign($xml);

    // Add signature
    $xml = $this->injectSignature($xml);
    foreach ($this->extensions as $ext) $xml = $ext->__onAfterSign($xml);

    // Prepend content type
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

    // Save document
    if (!is_null($filePath)) return file_put_contents($filePath, $xml);
    return $xml;
  }

}
