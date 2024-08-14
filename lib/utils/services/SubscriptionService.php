<?php

namespace Payarc\PayarcSdkPhp\utils\services;

class SubscriptionService extends BaseService
{

    public function cancel($subscription)
    {
        return $this->cancelSubscription($subscription);
    }

    public function list($params=[])
    {
        return $this->listSubscriptions($params);
    }

    public function update($subscription, $newData)
    {
        return $this->updateSubscription($subscription, $newData);
    }

    protected final function cancelSubscription($subscription)
    {

    }

    protected final function updateSubscription($subscription, $newData)
    {

    }

    private function listSubscriptions($params=[])
    {

    }
}