<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DenialReason: string implements HasLabel, HasColor
{
    case MemberBlacklisted = 'member_blacklisted';
    case MemberNotFound = 'member_not_found';
    case UnknownCredential = 'unknown_credential';
    case CredentialRevoked = 'credential_revoked';
    case NoActiveSubscription = 'no_active_subscription';
    case SubscriptionFrozen = 'subscription_frozen';
    case SubscriptionExpired = 'subscription_expired';
    case DepartmentNotCovered = 'department_not_covered';
    case NoSessionsRemaining = 'no_sessions_remaining';
    case OutstandingDues = 'outstanding_dues';
    case OutsideOffPeakHours = 'outside_off_peak_hours';
    case AgeRestriction = 'age_restriction';

    public function getLabel(): string
    {
        return match ($this) {
            self::MemberBlacklisted => 'Member Blacklisted',
            self::MemberNotFound => 'Member Not Found',
            self::UnknownCredential => 'Unknown Credential',
            self::CredentialRevoked => 'Credential Revoked',
            self::NoActiveSubscription => 'No Active Subscription',
            self::SubscriptionFrozen => 'Subscription Frozen',
            self::SubscriptionExpired => 'Subscription Expired',
            self::DepartmentNotCovered => 'Department Not Covered',
            self::NoSessionsRemaining => 'No Sessions Remaining',
            self::OutstandingDues => 'Outstanding Dues',
            self::OutsideOffPeakHours => 'Outside Off Peak Hours',
            self::AgeRestriction => 'Age Restriction',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MemberBlacklisted => 'danger',
            self::MemberNotFound => 'gray',
            self::UnknownCredential => 'gray',
            self::CredentialRevoked => 'gray',
            self::NoActiveSubscription => 'gray',
            self::SubscriptionFrozen => 'gray',
            self::SubscriptionExpired => 'gray',
            self::DepartmentNotCovered => 'gray',
            self::NoSessionsRemaining => 'gray',
            self::OutstandingDues => 'warning',
            self::OutsideOffPeakHours => 'gray',
            self::AgeRestriction => 'gray',
        };
    }
}
