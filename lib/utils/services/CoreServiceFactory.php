<?php

namespace Payarc\PayarcSdkPhp\utils\services;

class CoreServiceFactory extends BaseServiceFactory
{
    /**
     * @var array<string, string>
     */
    private $classMap = [
        'charges' => ChargeService::class,
        'customers' => CustomerService::class,
        'applications' => ApplicationService::class,
        'split_campaigns' => SplitCampaignService::class,
        'billing' =>[
            'plan' => PlanService::class,
            'plan_subscription' => SubscriptionService::class,
        ]
    ];

    public function __construct($client)
    {
        parent::__construct($client);
        $this->setClassMap($this->classMap);  // Pass the class map to the base factory
    }
    /**
     * Retrieves the service class or nested class map by name.
     *
     * @param string $name
     * @return null|string|array
     */
    protected function getServiceClass(string $name): string|array|null
    {
        return $this->classMap[$name] ?? null;
    }
}