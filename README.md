# Payarc SDK for PHP

The Payarc SDK allows developers to integrate Payarc's payment processing capabilities into their applications with ease. This SDK provides a comprehensive set of APIs to handle transactions, customer management, and candidate merchant management.

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Dependencies](#dependencies)
- [Usage](#usage)
- [API Reference](#api-reference)
- [Examples](#examples)
- [Contributing](#contributing)
- [License](#license)

## Requirements

PHP 8.1 or later.
## Installation

You can install the Payarc SDK using [composer](https://getcomposer.org/).Run the following command:

> [!WARNING]
> There is no stable version of this package yet. Use: **dev-master** for now.

```bash
composer require payarc/payarc-php-sdk:dev-master
```
To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):
    
```php
require_once('vendor/autoload.php');
```
## Dependencies

The bindings require the following extensions in order to work properly:

-   [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
-   [`json`](https://secure.php.net/manual/en/book.json.php)
-   [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php) (Multibyte String)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Usage

Before you can use the Payarc SDK, you need to initialize it with your API key and the URL base point. This is required for authenticating your requests and specifying the endpoint for the APIs. For each environment (prod, sandbox) both parameters have different values. This information should stay on your server and security measures must be taken not to share it with your customers. Provided examples use package [symfony/dotenv](https://packagist.org/packages/symfony/dotenv) to store this information and provide it on the constructor. It is not mandatory to use this approach as your setup could be different.
In case you want to take benefits of candidate merchant functionality you need so-called Agent identification token. This token could be obtained from the portal.

You have to create `.env` file in root of your project and update the following rows after =
```ini
PAYARC_BASE_URL=''
PAYARC_KEY=''
AGENT_KEY=''
PAYARC_VERSION=1
```
then install [symfony/dotenv](https://packagist.org/packages/symfony/dotenv) package

```bash
$ composer require symfony/dotenv
```
You have to create object from SDK to call different methods depends on business needs. Optional you can load `.env` file into configuration by adding the following code:
```php
use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load('.env');
```

then you create instance of the SDK

```php
/**
 * Creates an instance of Payarc.
 * @param {string} bearer_token - The bearer token for authentication.Mandatory parameter to construct the object
 * @param {string} [base_url='sandbox'] - The url of access points possible values prod or sandbox, as sandbox is the default one. Vary for testing playground and production. can be set in environment file too.
 * @param {string} [api_version='/v1/'] - The version of access points for now 1(it has default value thus could be omitted).
 * @param {string} [version='1.0'] - API version.
 * @param {string} bearer_token_agent - The bearer token for agent authentication. Only required if you need functionality around candidate merchant
 * 
 */
require_once '../vendor/autoload.php';
use Payarc\PayarcSdkPhp\Payarc;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load('.env');

$payarc = new Payarc(
    bearer_token: $_ENV['PAYARC_KEY'],
    base_url: $_ENV['PAYARC_BASE_URL'],
    version: $_ENV['PAYARC_VERSION'],
    bearer_token_agent: $_ENV['AGENT_KEY']
);
```

if no errors you are good to go.

## API Reference
- Documentation for existing payment API provided by Payarc can be found on https://docs.payarc.net/
- Documentation for existing candidate merchant management API can be found on https://docs.apply.payarc.net/

## Examples
SDK is build around object payarc. From this object you can access properties and function that will support your operations.

### Object `Payarc` has the following services:
    charges - to manipulate payments
    customers - to manipulate customers
    applications - to manipulate candidate merchants
    split_campaigns - to manipulate split campaigns
    billing
       - plan - to manipulate plans
       - plan_subscription - to manipulate Plan subscriptions

#### Service `Payarc->charges` is used to manipulate payments in the system. This Service has the following functions:
    create - this function will create a payment intent or charge accepting various configurations and parameters. See examples for some use cases. 
    retrieve - this function returns json object 'Charge' with details
    list - returns an object with attribute 'charges' a list of json object holding information for charges and object in attribute 'pagination'
    createRefund - function to perform a refund over existing charge

### Service ``Payarc->customer``
#### Service `Payarc->customer` is representing your customers with personal details, addresses and credit cards and/or bank accounts. Saved for future needs
    create - this function will create object stored in the database for a customer. it will provide identifier unique for each in order to identify and inquiry details. See examples and docs for more information
    retrieve - this function extract details for specific customer from database
    list - this function allows you to search amongst customers you had created. It is possible to search based on some criteria. See examples and documentation for more details  
    update - this function allows you to modify attributes of customer object.

### Service `Payarc->applications`
##### Service `Payarc->applications` is used by Agents and ISVs to manage candidate merchant when acquiring new customer. As such you can create, list, get details, and manage documents required in boarding process.
    create - this function add new candidate into database. See documentation for available attributes, possible values for some of them and which are mandatory. 
    list - returns a list of application object representing future merchants. Use this function to find the interested identifier. 
    retrieve - based on identifier or an object returned from list function, this function will return details 
    delete - in case candidate merchant is no longer needed it will remove information for it.
    add_document - this function is adding base64 encoded document to existing candidate merchant. For different types of document required in the process contact Payarc. See examples how the function could be invoked
    delete_document - this function removes document, when document is no longer valid.
    list_sub_agents - this function is usefull to create candidate in behalf of other agent.
    submit - this function initialize the process of sing off contract between Payarc and your client

### Service `Payarc->billing`
This Service is aggregating other services responsible for recurrent payments. Nowadays, they are `plan` and `plan_subscription`.

### Service `Payarc->billing->plan`
#### This Service contains information specific for each plan like identification details, rules for payment request and additional information. This SERVICE has methods for:
    create - you can programmatically created new objects to meet client's needs,
    list - inquiry available plans,
    retrieve - collect detailed information for a plan,
    update - modify details of a plan,
    delete - remove plan when no longer needed,
    create_subscription: issue a subscription for a customer from a plan.
Based on plans you can create subscription. Time scheduled job will request and collect payments (charges) according plan schedule from customer.

## Creating a Charge
### Example: Create a Charge with Minimum Information
To create a `payment(charge)` from a customer, minimum information required is:
- `amount` converted in cents,
- `currency` equal to 'usd',
- `source` the credit card which will be debited with the amount above.

For credit card minimum needed attributes are `card number` and `expiration date`. For full list of attributes see API documentation.
This example demonstrates how to create a charge with the minimum required information:
```php
try {
    $charge = $payarc->charges->create(
        [
            'amount' => 2860,
            'currency' => 'usd',
            'source' => [
                "card_number" => "4012******5439",
                "exp_month" => "03",
                "exp_year" => "2025",
            ]
        ],
    );
    echo "Charge created: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Example: Create a Charge by Token
To create a payment(charge) from a customer, minimum information required is:
- `amount` converted in cents,
- `currency` equal to 'usd',
- `source` an object that has attribute `token_id`. this can be obtained by the [CREATE TOKEN API](https://docs.payarc.net/#ee16415a-8d0c-4a71-a5fe-48257ca410d7) for token creation.
  This example shows how to create a charge using a token:

```php
 $charge_data = [
    'amount' => 1285,
    'currency' => 'usd',
    'source' => [
        "token_id" => "tok_mE*****LL8wYl"
    ]
];
try {
    $charge = $payarc->charges->create($charge_data);
    echo "Charge created: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Example: Create a Charge by Card ID

Charge can be generated over specific credit card (cc) if you know the cc's ID and customer's ID to which this card belongs.
This example demonstrates how to create a charge using a card ID:

```php
$charge_data = [
    'amount' => 3985,
    'currency' => 'usd',
    'source' => [
        'card_id' => 'card_Ly9*****59M0m1',
        'customer_id' => 'cus_j*******PVnDp'
    ]
];
try {
    $charge = $payarc->charges->create($charge_data);
    echo "Charge created: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
### Example: Create a Charge by Bank account ID

This example shows how to create an ACH charge when you know the bank account 

```php
try {
    $customer = $this->payarc->customers->retrieve('cus_j*******p');
    $charge_data = [
        'amount' => 3785,
        'sec_code'=> 'WEB',
        'source' => [
            'bank_account_id'=> 'bnk_eJjbbbbbblL'
        ]
    ];
    $charge =  $customer['charges']['create'](charge_data);
    echo "Charge created: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
Example make ACH charge with new bank account. Details for bank account are send in attribute source.
```php
try {
    $customer = $this->payarc->customers->retrieve('cus_j*******p');
    $charge_data = [
        'amount' => 3785,
        'sec_code'=> 'WEB',
        'source' => [
             'account_number' =>'123432575352',
             'routing_number'=>'123345349',
             'first_name'=> 'FirstName III',
             'last_name'=>'LastName III',
             'account_type'=> 'Personal Savings',
        ]
    ];
    $charge =  $customer['charges']['create'](charge_data);
    echo "Charge created: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

## Listing Charges

### Example: List Charges with No Constraints

This example demonstrates how to list all charges without any constraints:
    
```php
try {
    $charges = $payarc->charges->list();
    echo "Charges listed: " . json_encode($charges) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
## Retrieving a Charge

### Example: Retrieve a Charge

This example shows how to retrieve a specific charge by its ID:
    
```php
try {
    $charge = $payarc->charges->retrieve('ch_1J*****3');
    echo "Charge retrieved: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
``` 


### Example: Retrieve an ACH Charge

his example shows how to retrieve a specific ACH charge by its ID:
        
```php
try {
    $charge = $payarc->charges->retrieve('ach_1J*****3');
    echo "Charge retrieved: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
## Refunding a Charge

### Example: Refund a Charge

This example demonstrates how to refund either charge or ACH charge by its ID:
- for regular charge use `ch_` prefix and for ACH charge use `ach_` prefix.
        
```php
try {
    $id = 'ach_g**********08eA';
    $options = ['reason' => 'requested_by_customer',
                'description'=> 'The customer returned the product, did not like it']
    $charge = $payarc->charges->createRefund($id, $options);
    echo "Charge refunded: " . json_encode($charge) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

## Managing Customers

### Example: Create a Customer with Credit Card Information
This example shows how to create a new customer with credit card information: