<?php
namespace josemmo\Facturae\Controller;

use josemmo\Facturae\FacturaeItem;

/**
 * Implements all attributes and methods needed to make
 * @link{josemmo\Facturae\Facturae} instantiable.
 *
 * This includes all properties that define an electronic invoice, but without
 * additional functionalities such as signing or exporting.
 */
abstract class FacturaeProperties extends FacturaeConstants {

  /* ATTRIBUTES */
  protected $currency = 'COP';
  protected $language = 'es';
  protected $version = null;
  protected $header = array(
    'invoice_authorization' => null,
    'start_date' => null,
    'end_date' => null,
    'prefix' => null, 
    'number' => null, 
    'from' => null, 
    'to' => null, 
    'identification_code' => null, 
    'provider_id' => null, 
    'software_id' => null, 
    'software_security_code' => null
  );
  protected $parties = array(
    "seller" => null,
    "buyer" => null
  );
  protected $items = array();
  protected $legalLiterals = array();


  /**
   * Constructor for the class
   * @param string $schemaVersion If omitted, latest version available
   */
  public function __construct($schemaVersion=self::SCHEMA_1) {
    $this->setSchemaVersion($schemaVersion);
  }


  /**
   * Set schema version
   *
   * @param string $schemaVersion Facturae schema version to use
   */
  public function setSchemaVersion($schemaVersion) {
    $this->version = $schemaVersion;
  }


  /**
   * Set seller
   *
   * @param FacturaeParty $seller Seller information
   */
  public function setSeller($seller) {
    $this->parties['seller'] = $seller;
  }


  /**
   * Set buyer
   *
   * @param FacturaeParty $buyer Buyer information
   */
  public function setBuyer($buyer) {
    $this->parties['buyer'] = $buyer;
  }


  /**
   * Set invoice number
   *
   * @param string     $serie  InvoiceAuthorization code of the invoice
   * @param int|string $number Invoice number in given serie
   */
  public function setNumber($invoiceAuthorization, $number) {
    $this->header['invoice_authorization'] = $invoiceAuthorization;
    $this->header['number'] = $number;
  }


  /**
   * Set issue date
   *
   * @param int|string $date Issue date
   */
  public function setIssueDate($date) {
    $this->header['issueDate'] = is_string($date) ? strtotime($date) : $date;
  }


  /**
   * Set due date
   *
   * @param int|string $date Due date
   */
  public function setDueDate($date) {
    $this->header['dueDate'] = is_string($date) ? strtotime($date) : $date;
  }


  /**
   * Set billing period
   *
   * @param int|string $date Start date
   * @param int|string $date End date
   */
  public function setBillingPeriod($startDate=null, $endDate=null) {
    $this->header['start_date'] = $startDate;
    $this->header['end_date'] = $endDate;

    return $this;
  }

  /**
   * Set prefix
   *
   * @param string $prefix The prefix of the invoice
   */
  public function setPrefix($prefix)
  {
    $this->header['prefix'] = $prefix;

    return $this;
  }


  /**
   * Set Billing ranges
   *
   * @param int|string $from range from
   * @param int|string $to range to
   */
  public function setBillingRanges($from, $to)
  {
    $this->header['from'] = $from;
    $this->header['to'] = $to;

    return $this;
  }

  /**
   * Set identification code 
   *
   * @param string $code country code
   */
  public function setIdentificationCode($code)
  {
    $this->header['identification_code'] = $code;

    return $this;
  }

  /**
   * Set software provider
   *
   * @param int    $providerID service provider
   * @param string $softwareID software identifier
   */
  public function setSoftwareProvider($providerID, $softwareID)
  {
    $this->header['provider_id'] = $providerID;
    $this->header['software_id'] = $softwareID;

    return $this;
  }

  /**
   * Set Software Security Code
   *
   * @param string $softwarePIN Software PIN
   */
  public function setSoftwareSecurityCode($softwarePIN)
  {
    $softwareSecurityCode = $this->header['software_id'] . $softwarePIN;
    $this->header['software_security_code'] = hash('sha384', $softwareSecurityCode);

    return $this;
  }

  /**
   * Set dates
   *
   * This is a shortcut for setting both issue and due date in a single line
   *
   * @param int|string $issueDate Issue date
   * @param int|string $dueDate Due date
   */
  public function setDates($issueDate, $dueDate=null) {
    $this->setIssueDate($issueDate);
    $this->setDueDate($dueDate);
  }


  /**
   * Set payment method
   *
   * @param string $method Payment method
   * @param string $iban   Bank account in case of bank transfer
   */
  public function setPaymentMethod($method=self::PAYMENT_CASH, $iban=null) {
    $this->header['paymentMethod'] = $method;
    if (!is_null($iban)) $iban = str_replace(" ", "", $iban);
    $this->header['paymentIBAN'] = $iban;
  }


  /**
   * Set description
   * @param string $desc Invoice description
   */
  public function setDescription($desc) {
    $this->header['description'] = $desc;
  }


  /**
   * Set references
   * @param string $file        File reference
   * @param string $transaction Transaction reference
   * @param string $contract    Contract reference
   */
  public function setReferences($file, $transaction=null, $contract=null) {
    $this->header['fileReference'] = $file;
    $this->header['receiverTransactionReference'] = $transaction;
    $this->header['receiverContractReference'] = $contract;
  }


  /**
   * Add legal literal
   *
   * @param string $message Legal literal reference
   */
  public function addLegalLiteral($message) {
    $this->legalLiterals[] = $message;
  }


  /**
   * Add item
   *
   * Adds an item row to invoice. The fist parameter ($desc), can be an string
   * representing the item description or a 2 element array containing the item
   * description and an additional string of information.
   *
   * @param FacturaeItem|string|array $desc      Item to add or description
   * @param float                     $unitPrice Price per unit, taxes included
   * @param float                     $quantity  Quantity
   * @param int                       $taxType   Tax type
   * @param float                     $taxRate   Tax rate
   */
  public function addItem($desc, $unitPrice=null, $quantity=1, $taxType=null,
                          $taxRate=null) {
    if ($desc instanceOf FacturaeItem) {
      $item = $desc;
    } else {
      $item = new FacturaeItem([
        "name" => is_array($desc) ? $desc[0] : $desc,
        "description" => is_array($desc) ? $desc[1] : null,
        "quantity" => $quantity,
        "unitPrice" => $unitPrice,
        "taxes" => array($taxType => $taxRate)
      ]);
    }
    array_push($this->items, $item);
  }


  /**
   * Get totals
   *
   * @return array Invoice totals
   */
  public function getTotals() {
    // Define starting values
    $totals = array(
      "taxesOutputs" => array(),
      "taxesWithheld" => array(),
      "invoiceAmount" => 0,
      "grossAmount" => 0,
      "grossAmountBeforeTaxes" => 0,
      "totalTaxesOutputs" => 0,
      "totalTaxesWithheld" => 0
    );

    // Run through every item
    foreach ($this->items as $itemObj) {
      $item = $itemObj->getData();
      $totals['invoiceAmount'] += $item['totalAmount'];
      $totals['grossAmount'] += $item['grossAmount'];
      $totals['totalTaxesOutputs'] += $item['totalTaxesOutputs'];
      $totals['totalTaxesWithheld'] += $item['totalTaxesWithheld'];

      // Get taxes
      foreach (["taxesOutputs", "taxesWithheld"] as $taxGroup) {
        foreach ($item[$taxGroup] as $type=>$tax) {
          if (!isset($totals[$taxGroup][$type])) {
            $totals[$taxGroup][$type] = array();
          }
          if (!isset($totals[$taxGroup][$type][$tax['rate']])) {
            $totals[$taxGroup][$type][$tax['rate']] = array(
              "base" => 0,
              "amount" => 0
            );
          }
          $totals[$taxGroup][$type][$tax['rate']]['base'] += $item['totalAmountWithoutTax'];
          $totals[$taxGroup][$type][$tax['rate']]['amount'] += $tax['amount'];
        }
      }
    }

    // Fill rest of values
    $totals['grossAmountBeforeTaxes'] = $totals['grossAmount'];

    return $totals;
  }

}
