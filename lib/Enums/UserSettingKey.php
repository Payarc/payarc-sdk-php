<?php

namespace Payarc\PayarcSdkPhp\Enums;

/**
 * Enum for all User Setting Keys used in the SDK.
 */
enum UserSettingKey: string
{
    /** Onboarding Webhook URL */
    case ONBOARDING_WEBHOOK = 'merchant.onboarded.webhook';
    /** Lead Update Webhook URL */
    case LEAD_UPDATE_WEBHOOK = 'lead.updated.webhook';
    /** Lead Category Update Webhook URL */
    case LEAD_UPDATE_CATEGORY_WEBHOOK = 'lead.category.updated.webhook';
    /** Lead Underwriting Update Webhook URL */
    case LEAD_UNDERWRITING_UPDATED_WEBHOOK = 'lead.underwriting.updated.webhook';
}