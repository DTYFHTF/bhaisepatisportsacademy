<?php

namespace App\Filament\Pages;

use App\Enums\CheckInSource;
use App\Models\AccessCredential;
use App\Models\Department;
use App\Models\Member;
use App\Services\CheckInService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CheckInKiosk extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Access';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Check-in kiosk';

    protected static string $view = 'filament.pages.check-in-kiosk';

    public string $search = '';

    public ?string $selectedMemberId = null;

    public function updatedSearch(): void
    {
        $this->selectedMemberId = null;

        // A scanned QR/RFID identifier resolves straight to the member.
        if (strlen(trim($this->search)) >= 6) {
            $credential = AccessCredential::query()
                ->active()
                ->where('identifier_hash', AccessCredential::hashIdentifier($this->search))
                ->first();

            if ($credential) {
                $this->selectedMemberId = $credential->member_id;
                $this->search = '';
            }
        }
    }

    public function getResultsProperty(): Collection
    {
        $term = trim($this->search);

        if ($this->selectedMemberId || strlen($term) < 2) {
            return collect();
        }

        return Member::query()
            ->where(fn ($q) => $q
                ->where('phone', 'like', "%{$term}%")
                ->orWhere('member_code', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%"))
            ->limit(8)
            ->get();
    }

    public function getSelectedMemberProperty(): ?Member
    {
        return $this->selectedMemberId ? Member::find($this->selectedMemberId) : null;
    }

    public function getDepartmentsProperty(): Collection
    {
        $service = app(CheckInService::class);
        $member = $this->selectedMember;

        return Department::active()->accessControlled()->orderBy('sort_order')->get()
            ->map(function (Department $dept) use ($service, $member) {
                $result = $member ? $service->checkEligibility($member, $dept) : null;

                return [
                    'model' => $dept,
                    'allowed' => $result?->allowed,
                    'reason' => $result?->reason?->getLabel(),
                ];
            });
    }

    public function select(string $memberId): void
    {
        $this->selectedMemberId = $memberId;
        $this->search = '';
    }

    public function clearSelection(): void
    {
        $this->selectedMemberId = null;
        $this->search = '';
    }

    public function checkIn(string $departmentId): void
    {
        $member = $this->selectedMember;

        if (! $member) {
            return;
        }

        $checkIn = app(CheckInService::class)->checkIn(
            $member,
            Department::findOrFail($departmentId),
            CheckInSource::FrontDesk,
            staff: auth()->user(),
        );

        if ($checkIn->was_allowed) {
            Notification::make()->success()
                ->title("{$member->full_name} — checked in")
                ->body($checkIn->session_consumed ? 'One session consumed.' : null)
                ->send();

            $this->clearSelection();
        } else {
            Notification::make()->danger()
                ->title('Entry denied')
                ->body($checkIn->denial_reason?->getLabel())
                ->persistent()
                ->send();
        }
    }
}
