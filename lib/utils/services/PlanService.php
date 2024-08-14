<?php

namespace Payarc\PayarcSdkPhp\utils\services;

class PlanService extends BaseService
{
    public function create($data)
    {
        return $this->create_plan($data);
    }

    public function list($params)
    {
        return $this->list_plans($params);
    }

    public function retrieve($params)
    {
        return $this->getPlan($params);
    }

    public function update($params, $new_data)
    {
        return $this->updatePlan($params, $new_data);
    }

    public function delete($params)
    {
        return $this->deletePlan($params);
    }

    public function create_subscription($params, $new_data)
    {
        return $this->createSubscription($params, $new_data);
    }


    private function create_plan($data=[])
    {

    }

    private function list_plans($params=[])
    {

    }

    protected final function getPlan($params)
    {

    }

    protected final function updatePlan($plan, $newData)
    {

    }

    protected final function deletePlan($params)
    {

    }
    protected final function createSubscription($plan, $newData)
    {

    }
}