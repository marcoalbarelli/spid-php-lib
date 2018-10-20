<?php

namespace Italia\Spid\Spid\Interfaces;

// service provider class
use Italia\Spid\Spid\Saml\Idp;

interface SAMLInterface
{
    // $settings = [
    //     'sp_entityid' => SP_BASE_URL, // preferred: https protocol, no trailing slash, example: https://sp.example.com/
    //     'sp_key_file' => '/path/to/sp.key',
    //     'sp_cert_file' => '/path/to/sp.crt',
    //     'sp_assertionconsumerservice' => [
    //         // order is important ! the 0-base index in this array will be used as ID in the calls
    //         SP_BASE_URL . '/acs',
    //         ...
    //     ],
    //     'sp_singlelogoutservice' => [
    //         // order is important ! the 0-base index in this array will be used as ID in the calls
    //         [SP_BASE_URL . '/slo', 'POST'],
    //         [SP_BASE_URL . '/slo', 'REDIRECT']
    //         ...
    //     ],
    //     'sp_org_name' => 'your organization full name',
    //     'sp_org_display_name' => 'your organization display name',
    //     'idp_metadata_folder' => '/path/to/idp_metadata/',
    //     'sp_attributeconsumingservice' => [
    //         // order is important ! the 0-base index in this array will be used as ID in the calls
    //         ["fiscalNumber"],
    //         ["name", "familyName", "fiscalNumber", "email", "spidCode"],
    //         ...
    //     ];
    public function __construct(array $settings);

    // loads an Idp object by parsing the provided XML at $filename
    // $filename: file name of the IdP to be loaded. Only the file, without the path, needs to be provided.
    // returns null or the Idp object.
    public function loadIdpFromFile(string $filename);

    // loads all the `Idp` objects from the idp_metadata_folder provided in settings
    // the individual files are loaded with loadIdpFromFile($filename)
    // returns an array mapping filename (without extension) => entityID (used for spid-smart-button)
    // if no IdPs are found returns an empty array
    public function getIdpList() : array;

    // alias of loadIdpFromFile
    public function getIdp(string $filename);

    // returns SP metadata as a string
    public function getSPMetadata() : string;
    
    // performs login with REDIRECT binding
    // $idpFilename: shortname of IdP, same as the name of corresponding IdP metadata file, without .xml extension
    // $assertID: index of assertion consumer service as per the SP metadata
    // $attrID: index of attribute consuming service as per the SP metadata
    // $level: SPID level (1, 2 or 3)
    // $returnTo: url to redirect to after login
    // $shouldRedirect: tells if the function should emit headers and redirect to login URL or return the URL as string
    // returns false is already logged in
    // returns an empty string if $shouldRedirect = true, the login URL otherwhise
    public function login(
        string $idpFilename,
        int $assertID,
        int $attrID,
        $level = 1,
        string $redirectTo = null,
        $shouldRedirect = true
    );

    // performs login with POST Binding
    // uses the same parameters and return values as login
    public function loginPost(
        string $idpFilename,
        int $assertID,
        int $attrID,
        $level = 1,
        string $redirectTo = null,
        $shouldRedirect = true
    );

    // This method takes the necessary steps to update the user login status, and return a boolean representing the
    // result.
    // The method checks for any input response and validates it. The validation itself can create or destroy login
    // sessions.
    // After updating the login status as described, return true if login session exists, false otherwise
    // IMPORTANT NOTICE: AFTER ANY LOGIN/LOGOUT OPERATION YOU MUST CALL THIS METHOD TO FINALIZE THE OPERATION
    // CALLING THIS METHOD AFTER LOGIN() WILL IN FACT FINISH THE OPERATION BY VALIDATING THE RESULT AND CREATING THE
    // SESSION AND STORING USER ATTRIBUTES.
    // SIMILARLY, AFTER A LOGOUT() CALLING THIS METHOD WILL VALIDATE THE RESULT AND DESTROY THE SESSION.
    // LOGIN() AND LOGOUT() ALONE INTERACT WITH THE IDP, BUT DON'T CHECK FOR RESULTS AND UPDATE THE SP
    public function isAuthenticated() : bool;

    // performs logout with REDIRECT binding
    // $slo: index of the singlelogout service as per the SP metadata
    // $returnTo: url to redirect to after logout
    // $shouldRedirect: tells if the function should emit headers and redirect to logout URL or return the URL as string
    // returns false if not logged in
    // returns an empty string if $shouldRedirect = true, the logout URL otherwhise
    public function logout(int $slo, string $redirectTo = null, $shouldRedirect = true);

    // performs logout with POST Binding
    // uses the same parameters and return values as logout
    public function logoutPost(int $slo, string $redirectTo = null, $shouldRedirect = true);

    // returns attributes as an array or an empty array if not authenticated
    // example: array('name' => 'Franco', 'familyName' => 'Rossi', 'fiscalNumber' => 'FFFRRR88A12T4441R',)
    public function getAttributes() : array;

    // returns true if the SP certificates are found where the settings says they are, and they are valid
    // (i.e. the library has been configured correctly
    public function isConfigured() : bool;
    
    // Generates with openssl the SP certificates where the settings says they should be
    // this function should be used with care because it requires write access to the filesystem, and invalidates the metadata
    public function generateCerts(string $countryName, string $stateName, string $localityName, string $commonName, string $emailAddress);
}
