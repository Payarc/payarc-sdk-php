<?php
/**
 * Creates an instance of Payarc.
 * @param {string} bearer_token - The bearer token for authentication.Mandatory parameter to construct the object
 * @param {string} [base_url='sandbox'] - The url of access points possible values prod or sandbox, as sandbox is the default one. Vary for testing playground and production. can be set in environment file too.
 * @param {string} [api_version='/v1/'] - The version of access points for now 1(it has default value thus could be omitted).
 * @param {string} [version='1.0'] - API version.
 * @param {string} bearer_token_agent - The bearer token for agent authentication. Only required if you need functionality around candidate merchant
 * 
 */
require_once 'vendor/autoload.php';
use Payarc\PayarcSdkPhp\Payarc;

$bearerToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI0Mzg4IiwianRpIjoiOTI0ODMyZjJiM2Q1MDZiZjU1M2Q0NWQzMWJkNTg0MWQ0ZWRjMjdmMjI4ODg4NWU4NWQzMDdmNjk3MWJmYjMxMTJhZjYyYzhmN2MyZTlhZTciLCJpYXQiOjE2MTExNzUxNjgsIm5iZiI6MTYxMTE3NTE2OCwiZXhwIjoxNzY4ODU1MTY4LCJzdWIiOiIxNTY1MyIsInNjb3BlcyI6IioifQ.bYo6ZQ4Jg3wjT_KibvLGpmTpWgapBfyJOXxH-1boMbVyzmj9oO_o8NpLu4aR8vGt4ZcCwmqWkuAJkYdDij0DeDuqI_7IJcBK7hRHBR4tjRbo2plmc44xnxFp5G-NbXC3lj620L2lfgBheyMRAhpkaLfwaVBQvOsq829kNmSlPhom_OhTmyBEDZi5oTFg44vKi4LfI9gORlV0wBFELrcjWoodTsMJHDk_Tiuxwkdf81XvaM6uIiJUTgnnPZM4LDINHbi9YQZ7HYORSIFn2gOyfdGSwTiY5gi13vC-ISDZxBxQWN61JMEwIheaFTubmNgUTvn7gSsp8rnSLo1Hm7p_Mh5lg6Jf2Z89509KRgO5X3iQMWMWmvAX3leSYUi0ngAXQBGdEHlyUNNy0S3dh-fJzkyFpQxkftUDX3ZKbJxCd4H4Vfe5WpgmEdjhD2wb6RI1GnPBkG6SwGy6kcHGjNKxK4hFBKZPCSwWJD7VgJP-eXQMU2J-i9tcc-zp4Acb4qjWe02FYBMKxY6FmDpFpLSvRZGXdH5Xegw6kfDIZWJF-mOB5g0ISFC_tjfxza544iEIOXlYkKzkCNXO0XbJUH6XFFv0Obd74VBrfPaHR-zxbgDmqHFRH_6bWIGAbwiwK3S8GG5RwDpk5uvEaC2F6V0M_o7ePEint8u6BCCK8WYPm7g";
$baseUrl = "prod";
$version = "1.0";

$payarc = new Payarc(
    bearer_token: $bearerToken,
    base_url: $baseUrl,
    version: $version
);

/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->sale("CREDIT", "REF300", "150", "1850406725");
    print_r($result);
} */
/* 
$login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->void("onDXOXonnbbonObL", "1850406725");
    print_r($result);
}
 */
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->refund("15","DLbnOBLXXWyXoOoM", "1850406725");
    print_r($result);
}
 */
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->refund("15","DLbnOBLXXWyXoOoM", "1850406725");
    print_r($result);
} */
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->blindCredit("REF31", "50", "IYmDAxNtma7g5228", "0227","1850406725");
    print_r($result);
}
 */
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->auth("REF21", "1000","1850406725");
    print_r($result);
}
 */
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->postAuth("REF212", "31", "500","1850406725");
    print_r($result);
}
 */
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->lastTransaction("1850406725");
    print_r($result);
} */
$login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->serverInfo();
    print_r($result);
}
/* $login = $payarc->payarcConnect->login();
if ($login) {
    $result = $payarc->payarcConnect->terminals();
    print_r($result);
}
 */