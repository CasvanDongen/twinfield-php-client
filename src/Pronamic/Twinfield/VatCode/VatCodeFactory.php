<?php

namespace Pronamic\Twinfield\VatCode;

use Pronamic\Twinfield\Factory\FinderFactory;
use Pronamic\Twinfield\Request\Read\Vat;

/**
 * VatCodeFactory
 *
 * A facade factory to make interaction with the the twinfield service easier
 * when trying to retrieve or send information about VAT codes.
 *
 * If you require more complex interactions or a heavier amount of control
 * over the requests to/from then look inside the methods or see
 * the advanced guide detailing the required usages.
 *
 * @package Pronamic\Twinfield
 * @subpackage VatCode
 * @author Emile Bons <emile@emilebons.nl>
 */
class VatCodeFactory extends FinderFactory
{
    /**
     * List all VAT codes.
     * @param string $pattern The search pattern. May contain wildcards * and ?
     * @param int $field The search field determines which field or fields will be searched. The available fields
     * depends on the finder type. Passing a value outside the specified values will cause an error.
     * @param int $firstRow First row to return, useful for paging
     * @param int $maxRows Maximum number of rows to return, useful for paging
     * @param array $options The Finder options. Passing an unsupported name or value causes an error. It's possible to
     * add multiple options. An option name may be used once, specifying an option multiple times will cause an error.
     * @return VatCode[] the VAT codes found
     */
    public function listAll($pattern = '*', $field = 0, $firstRow = 1, $maxRows = 100, $options = array())
    {
        $response = $this->searchFinder(self::TYPE_VAT_CODES, $pattern, $field, $firstRow, $maxRows, $options);
        $vatCodes = [];
        if($response->data->TotalRows !== 0) {
            foreach($response->data->Items->ArrayOfString as $vatCodeArray)
            {
                $vatCode = new VatCode();
                $vatCode->setCode($vatCodeArray->string[0]);
                $vatCode->setName($vatCodeArray->string[1]);
                $vatCodes[] = $vatCode;
            }
        }
        return $vatCodes;
    }

    public function get($code, $office = null)
    {
        // Attempts to process the login
        if ($this->getLogin()->process()) {

            // Get the secure service class
            $service = $this->getService();

            // No office passed, get the office from the Config
            if (! $office) {
                $office = $this->getConfig()->getOffice();
            }
            
            // Make a request to read a single Vat. Set the required values
            $request_vat = new Vat();
            $request_vat
                ->setOffice($office)
                ->setCode($code);

            // Send the Request document and set the response to this instance.
            $response = $service->send($request_vat);
            $this->setResponse($response);

            // Return result if successful or false if not.
            if ($response->isSuccessful()) {
                return $response->getResponseDocument();
            } else {
                return false;
            }
        }
    }

}