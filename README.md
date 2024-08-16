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

```php
 $customer_data = [
            "email" => "anon+50@example.com",
            "cards" => [
                [
                    "card_source" => "INTERNET",
                    "card_number" => "4012000098765439",
                    "exp_month" => "07",
                    "exp_year" => "2025",
                    "cvv" => "997",
                    "card_holder_name" => "Bat Doncho",
                    "address_line1" => "123 Main Street",
                    "city" => "Greenwich",
                    "state" => "CT",
                    "zip" => "06830",
                    "country" => "US",
                ],
                [
                    "card_source" => "INTERNET",
                    "card_number" => "4012000098765439",
                    "exp_month" => "01",
                    "exp_year" => "2025",
                    "cvv" => "998",
                    "card_holder_name" => "Bat Gancho",
                    "address_line1" => "123 Main Street Apt 44",
                    "city" => "Greenwich",
                    "state" => "CT",
                    "zip" => "06830",
                    "country" => "US",
                ]
            ]
        ];

        try {
            $customer = $payarc->customers->create($customer_data);
            echo "Customer created: " . json_encode($customer) . "\n";
        } catch (Throwable $e) {
            echo "Error detected: " . $e->getMessage() . "\n";
        }
```
### Example: Update a Customer

This example demonstrates how to update an existing customer's information when only ID is known:
```php
 
 try {
    $id = 'cus_j*******p';
    $customer = $payarc->customers->update($id, [
        "name" => "Bai Doncho 3",
        "description" => "Example customer",
        "phone" => "1234567890"
    ]);
    echo "Customer updated: " . json_encode($customer) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Example: Update an Already Found Customer
This example shows how to update a customer object:
```php

try {
    $customer = $payarc->customers->retrieve('cus_j*******p');
    $customer = $customer['update']([
        "name" => "Bai Doncho 4",
        "description" => "Senior Example customer",
        "phone" => "1234567895"
    ]);
    echo "Customer updated: " . json_encode($customer) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
### Example: List Customers with a Limit

This example demonstrates how to list customers with a specified limit:
```php
try {
    $customers = $payarc->customers->list(['limit' => 3]);
    echo "Customers retrieved: " . json_encode($customers) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Example: Add a New Card to a Customer

This example shows how to add a new card to an existing customer:
```php
 $card_data = [
            "card_source" => "INTERNET",
            "card_number" => "4012000098765439",
            "exp_month" => "01",
            "exp_year" => "2025",
            "cvv" => "998",
            "card_holder_name" => "Bat Gancho",
            "address_line1" => "123 Main Street Apt 44",
            "city" => "Greenwich",
            "state" => "CT",
            "zip" => "06830",
            "country" => "US",
        ];
try {
    $customer = $payarc->customers->retrieve($id);
    $card = $customer['cards']['create']($card_data);
    echo "Card added: " . json_encode($card) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Example: Add a New Bank Account to a Customer

This example shows how to add new bank account to a customer. See full list of bank account attributes in API documentation.
```php
 $acc_data = [
            "account_number" => "1234567890",
            "routing_number" => "110000000",
            'first_name' => 'Bat Petio',
            'last_name' => 'The Tsar',
            "account_type" => "Personal Savings",
            'sec_code' => 'WEB'
        ];
try {
    $customer = $payarc->customers->retrieve($id);
    $acc = $customer['bank_accounts']['create']($acc_data);
    echo "Bank account added: " . json_encode($acc) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

## Manage Candidate Merchants
### Create new Candidate Merchant
In the process of connecting your clients with Payarc a selection is made based on Payarc's criteria. Process begins with filling information for the merchant and creating an entry in the database. Here is an example how this process could start
```php
 $merchant_candidate = [
            "Lead" => [
                "Industry" => "cbd",
                "MerchantName" => "Kolio i sie",
                "LegalName" => "Best Co in w",
                "ContactFirstName" => "Joan",
                "ContactLastName" => "Dhow",
                "ContactEmail" => "contact+25@mail.com",
                "DiscountRateProgram" => "interchange"
            ],
            "Owners" => [
                [
                    "FirstName" => "First",
                    "LastName" => "Last",
                    "Title" => "President",
                    "OwnershipPct" => 100,
                    "Address" => "Somewhere",
                    "City" => "City Of Test",
                    "SSN" => "4546-0034",
                    "State" => "WY",
                    "ZipCode" => "10102",
                    "BirthDate" => "1993-06-24",
                    "Email" => "nikoj@negointeresuva2.com",
                    "PhoneNo" => "2346456784"
                ]
            ]
        ];

try {
    $merchant = $payarc->applications->create($merchant_candidate);
    echo "Merchant candidate created: " . json_encode($merchant) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
In this example attribute `Lead` is an object representing the business as the attribute `Owners` is and array of objects representing the owners of this business. Note this is the minimum information required. For successful boarding you should provide as much information as you can, for reference see documentation. In some case the logged user has to create application in behalf of some other agent. in this case the `object_id` of this agent must be sent in the object sent to function `payarc->applications->create`.To obtain the list of agent you can use function `list_sub_agents` as it is shown on examples:
```php
 $merc_candidate = [
            "Lead" => [
                "Industry" => "cbd",
                "MerchantName" => "chichovoto",
                "LegalName" => "Best Co in w",
                "ContactFirstName" => "Lubo",
                "ContactLastName" => "Penev",
                "ContactEmail" => "penata@chichovoto.com",
                "DiscountRateProgram"=> "interchange"
            ],
            "Owners" => [
                [
                    "FirstName" => "First",
                    "LastName" => "Last",
                    "Title" => "President",
                    "OwnershipPct" => 100,
                    "Address" => "Somewhere",
                    "City" => "City Of Test",
                    "SSN" => "4546-0034",
                    "State" => "WY",
                    "ZipCode" => "10102",
                    "BirthDate" => "1993-06-24",
                    "Email" => "nikoj@negointeresuva.com",
                    "PhoneNo" => "2346456784"
                ]
            ]
        ];
try {
    $sub_agent = $payarc->applications->list_sub_agents();
    $merc_candidate['agentId'] = $sub_agent['sub_agents'][0]['object_id'] ?? null;
    $candidate = $this->payarc->applications->create($merc_candidate);
    echo "Merchant candidate created: " . json_encode($candidate) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Retrieve Information for Candidate Merchant
To continue with onboarding process you might need to provide additional information or to inquiry existing leads. In the SDK  following functions exists: `list` and `retrieve`.

List all candidate merchant for current agent
```php
try {
    $applications = $payarc->applications->list();
    echo "Applications retrieved: " . json_encode($applications) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
Retrieve data for current candidate merchant
```php
try {
    $id = 'app_1J*****3';
    $merchant = $payarc->applications->retrieve($id);
    echo "Merchant candidate retrieved: " . json_encode($merchant) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
Update properties of candidate merchant
```php
$id = 'app_1J*****3';
$payload = [
            "MerchantBankAccountNo"=> "999999999",
            "MerchantBankRoutingNo"=> "1848505",
            "BankInstitutionName"=> "Bank of Kolio"
        ];
try{
    $updated_candidate = $payarc
                            ->applications
                            ->update($id, $payload);
    echo "Candidate merchant updated: " . json_encode($updated_candidate) . "\n";
}catch (Throwable $e){
    echo "Error detected: " . $e->getMessage() . "\n";
}
```


### Documents management
SDK is providing possibility of adding or removing documents with `add_document` and `delete_document` respectively.

Example for adding supportive documents to candidate merchant
```php
$doc_data = [
            "DocumentType"=> "Business Bank Statement",
            "DocumentName"=> "sample document 1",
            "DocumentIndex"=> 12246,
            "DocumentDataBase64"=> "data:image/jpeg;base64,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 "
        ];
try {
    $merchant = $payarc->applications->retrieve($id);
    $doc = $merchant['data']['add_document']($doc_data);
    echo "Document added: " . json_encode($doc) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
In this example we search for all candidate merchants and on the last added in the system we attach a document (Payarc logo) that will be used in on boarding process. See documentation for document attributes.
In case document is no longer needed you can see those examples
```php
try{
   $response = $payarc->applications->list();
      $applicant = $response['applications'][0];
      $details = $applicant['retrieve']();
      $document = $details['Documents']['data'][0] ?? null;
      if($document){
          $doc = $document['delete']();
          echo "Document deleted: " . json_encode($doc) . "\n";
      } else {
          echo "No document to delete\n";
      }
}catch (Throwable $e) {
   echo "Error detected: " . $e->getMessage() . "\n";
}
```
Again we search for the last candidate and remove first found (if exists) document. In case we already know the document ID, for example if we retrieve information for candidate you can use

```php
try{
    $id = 'doc_1J*****3';
    $doc = $payarc->applications->delete_document($id);
    echo "Document deleted: " . json_encode($doc) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Signature
As agent or ISV the process is completed once the contract between Payarc and your client is sent to this client for signature. Once all documents and data is collected method `submit` of the candidate merchant must be invoked, here is an example
```php
try{
    $id = 'app_1J*****3';
    $applicant = $payarc->applications->submit($id);
    echo "Applicant submitted for signature: " . json_encode($applicant) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
## Split Payment

As ISV you can create campaigns to manage financial details around your processing merchants. In the SDK the object representing this functionality is `split_campaigns` this object has functions to create. list, update campaigns. Here below are examples related to manipulation of campaign.


### List all campaigns
To inquiry all campaigns available for your agent
```php
try{
    $campaigns = $payarc->split_campaigns->list();
    echo "Campaigns retrieved: " . json_encode($campaigns) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

as result a list of campaigns is returned. based on this list you can update details


### List all processing merchants

Use this function to get collection of processing merchants. Later on you can assign campaigns to them
```php
try{
    $merchants = $payarc->split_campaigns->list_accounts();
    echo "Merchants retrieved: " . json_encode($merchants) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Create and retrieve details for campaign

Use this function to create new campaign
```php
try{
    $campaign = $payarc->split_campaigns->create(
        [
            'name'=> 'Mega bonus',
            'description'=> "Compliment for my favorite customers",
            'notes'=> "Only for VIPs",
            'base_charge'=> 63.33,
            'perc_charge'=> 5.7,
            'is_default'=> '0',
            'accounts'=> []
        ]
    );
    echo "Campaign created: " . json_encode($campaign) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
as result the new campaign is returned use it as an object of reference to `object_id`. IF you need to query details about the campaign see the example below.
```php
try{
    $id = 'cmp_o3**********86n5';
    $campaign = $payarc->split_campaigns->retrieve($id);
    echo "Campaign retrieved: " . json_encode($campaign) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
### Update campaign details

In case you need to update details of the campaign use `update` function. in the examples below you can reference campaign by id or as an object
```php
 try{
    $payload = [
        'notes'=> "new version of notes"
    ];
    $campaign = $payarc
        ->split_campaigns
        ->update($id, $payload);
    echo "Campaign updated: " . json_encode($campaign) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
## Recurrent Payments Setup
Recurrent payments, also known as subscription billing, are essential for any service-based business that requires regular, automated billing of customers. By setting up recurrent payments through our SDK, you can offer your customers the ability to easily manage subscription plans, ensuring timely and consistent revenue streams. This setup involves creating subscription plans, managing customer subscriptions, and handling automated billing cycles. Below, we outline the steps necessary to integrate recurrent payments into your application using our SDK.

### Creating Subscription Plans
The first step in setting up recurrent payments is to create subscription plans. These plans define the billing frequency, pricing, and any trial periods or discounts. Using our SDK, you can create multiple subscription plans to cater to different customer needs. Here is an example of how to create a plan:
```php
try{
    $data = [
            'name' => 'Monthly billing regular',
            'amount' => 999,
            'interval' => 'month',
            'statement_descriptor' => '2024 MerchantT. srvces'
     ];
    $plan = $payarc->billing->plan->create($data);
    echo "Plan created: " . json_encode($plan) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
In this example a new plan is created in attribute `name` client friendly name of the plan must be provided. Attribute `amount` is number in cents. in `interval` you specify how often the request for charge will occurs. Information in `statement_descriptor` will be present in the reason for payment request. For more attributes and details check API documentation.

### Updating Subscription Plan
Once plan is created sometimes it is required details form it to be changed. The SDK allow you to manipulate object `plan` or to refer to the object by ID. here are examples how to change details of a plan:
```php
try{
    $plans = $payarc->billing->plan->list();
    $plan = $plans['plans'][0];
    if($plan){
        $plan = $plan['update'](['name'=> 'New plan name']);
        echo "Plan updated: " . json_encode($plan) . "\n";
    }
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
Update plan when know the ID
```php
try{
    $id = 'plan_3aln*******8y8';
    $plan = $payarc->billing->plan->update($id, ['name'=> 'New plan name']);
    echo "Plan updated: " . json_encode($plan) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
### Creating Subscriptions
Once you have created subscription plans, the next step is to manage customer subscriptions. This involves subscribing customers to the plans they choose and managing their billing information. Our SDK makes it easy to handle these tasks. Here's how you can subscribe a customer to a plan:

#### Create a subscription over `plan` object
```php
try{
    $plans = $payarc->billing->plan->list(['search'=> 'iron']);
    $subscriber = [
        'customer_id'=> 'cus_*******AMNNVnjA',
    ];
    $plans = $plans['plans'];
    if($plans){
        $plan = $plans[0];
        if($plan){
            $subscription = $plan['create_subscription']($subscriber);
            echo "Subscription created: " . json_encode($subscription) . "\n";
        }
    }
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

#### # Create a subscription with plan id
```php
try{
    $subscriber = [
        'customer_id'=> 'cus_DPNMVjx4AMNNVnjA',
    ];
    $subscription = $payarc->billing->plan->create_subscription($id, $subscriber);
    echo "Subscription created: " . json_encode($subscription) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
This code subscribes a customer to the premium plan using their saved payment method. The SDK handles the rest, including storing the subscription details and scheduling the billing cycle.


### Listing Subscriptions
To collect already created subscriptions you can use method `list` as in the example 
```php
try{
    $subscriptions = $payarc->billing->plan_subscription->list(['limit'=> 3, 'plan'=>'plan_7****f']);
    echo "Subscriptions: " . json_encode($subscriptions) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
### Updating Subscription
To manipulate subscription SDK is providing few methods `update` and `cancel`, both can be used with identifier of subscription or over subscription object. Examples of their invocations:
#### Update subscription with ID
```php
try{
    $id = 'sub_7****f';
    $subscription = $payarc->billing->plan_subscription->update($id, ['description'=> 'Monthly for VIP']);
    echo "Subscription updated: " . json_encode($subscription) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
#### Cancel subscription with ID
```php
try{
    $id = 'sub_7****f';
    $subscription = $payarc->billing->plan_subscription->cancel($id);
    echo "Subscription canceled: " . json_encode($subscription) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
## Manage Disputes
A dispute in the context of payment processing refers to a situation where a cardholder questions the validity of a transaction that appears on their statement. This can lead to a chargeback, where the transaction amount is reversed from the merchant's account and credited back to the cardholder. A cardholder sees a transaction on their account that they believe is incorrect or unauthorized. This could be due to various reasons such as fraudulent activity, billing errors, or dissatisfaction with a purchase. The cardholder contacts their issuing bank to dispute the transaction. They may provide details on why they believe the transaction is invalid. The issuing bank investigates the dispute. This may involve gathering information from the cardholder and reviewing the transaction details. The issuing bank communicates the dispute to the acquiring bank (the merchant's bank) through the card network (in your case Payarc). The merchant is then required to provide evidence to prove the validity of the transaction, such as receipts, shipping information, or communication with the customer. Based on the evidence provided by both the cardholder and the merchant, the issuing bank makes a decision. If the dispute is resolved in favor of the cardholder, a chargeback occurs, and the transaction amount is deducted from the merchant's account and credited to the cardholder. If resolved in favor of the merchant, the transaction stands.
This documentation should help you understand how to use the Payarc SDK to manage charges and customers. If you have any questions, please refer to the Payarc API documentation or contact support.


### Inquiry Dispute
The SDK provide a function to list your disputes. you can provide query parameters to specify the constraints over the function. when sent with no parameters it returns all disputes in the past one month
```php
try{
    $cases = $payarc->disputes->list();
    echo "Cases: " . json_encode($cases) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

You can get details for a dispute by `retrieve` function. the identifier is returned by `list` function
```php
try{
    $id = 'dis_7****f';
    $cases = $payarc->disputes->retrieve($id);
    echo "Case: " . json_encode($cases) . "\n";
}catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```

### Submit Cases
In order to resolve the dispute in your(merchant's) flavour, the merchant is  required to provide evidence to prove the validity of the transaction, such as receipts, shipping information, or communication with the customer. The SDK provides a function `add_document` that allows you to provide files and write messages to prove that you have rights to keep the mony for the transaction. First parameter of this function is the identifier of the dispute for which the evidence is. Next parameter is an object with following attributes:
- `DocumentDataBase64`: base46 representation of the files that will be used as evidence
- `text`: short text to describe the evidence
- `mimeType`: type of the provided file
- `message`: Description of submitted case
  For more information for parameters and their attributes check documentation
```php
$document_base64 = "iVBORw0KGgoAAAANSUhEUgAAAIUAAABsCAYAAABEkXF2AAAABHNCSVQICAgIfAhkiAAAAupJREFUeJzt3cFuEkEcx/E/001qUQ+E4NF48GB4BRM9+i59AE16ANlE4wv4Mp5MjI8gZ+ONEMJBAzaWwZsVf2VnstPZpfb7STh06ewu5JuFnSzQ8d5vDfiLa3sHcHiIAoIoIIgCgiggitwbWM/f2vniTe7NoIZ7Dz9Y0X0qy7NHYfbLtn6dfzOoYXPlUl4+IIgCooGXj10ngzM77p81vVmY2Y9vL+xi9Tn4f41HYVZYx3Wb3yws9oWBlw8IooAgCgiigCAKCKKAIAoIooAgCoikGU3nqpvy3qesPvv6+/2+LZfLpHUcsrrPD0cKCKKAIAoIooAgCgiigCAKCOecs7q3iJXbZDLZWVaWZfR4733lLbfZbBbchzZvvV4vy+PmSAFBFBBEAUEUEEQBQRQQRAFR5DzfD81FxMxVpMg9l3HT938fjhQQRAFBFBBEAUEUEEQBQRQQRe5z7SptnYejGkcKCKKAIAoIooAgCgiigCAKiKQoYj6bMB6Pd8aMRqPoz22kfCalzfmXm45nDoIoIIgCgiggiAKCKCCIAiJrFKnfTxHS9vdX5P7+ibZwpIAgCgiigCAKCKKAIAoIooDomNl2352hc+WY3+NYzyf2c345V3EyGNmdwevo8anbr3Lbfu/j+9fndrH69Ofv+48+WtF9JuM4UkAQBQRRQBAFBFFAEAUEUUBUfo9m6jUPzjl7eWr26vRyWVmW9u59GT2+Suo1B4vFImn8/4ojBQRRQBAFBFFAEAUEUUAQBUTHe7/3eorUeYrQ9RSprmP/UtZ/6OP/xfUUqI0oIIgCgiggiqY36Ddz25x/uZZ1PXmcNj60H6H1H/p4sV1F/VvjZx84HJx9IFrl733wexy3U/b3FO7ogR0dD7OsezqdVt4/HFZvNzQ+t9T9C40P6ty9erElfEKsbblnDHNrekYzFu8pIIgCgiggiAKCKCAqzz5Ccr+7T3133fb1DG0//ro4UkAQBQRRQBAFBFFAEAXEb3wL3JblytFeAAAAAElFTkSuQmCC";
try {
    $case = $payarc->disputes->add_document('dis_MV***********AW0', [
        'DocumentDataBase64' => $document_base64,
        'text' => 'test doc evidence 3'
    ]);
    echo "Document added: " . json_encode($case) . "\n";
} catch (Throwable $e) {
    echo "Error detected: " . $e->getMessage() . "\n";
}
```
## License [MIT](LICENSE)