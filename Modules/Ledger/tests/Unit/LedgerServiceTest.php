<?php

namespace Modules\Ledger\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounts\Models\Account;
use Modules\Core\Enums\AccountStatus;
use Modules\Core\Enums\LedgerDirection;
use Modules\Core\Enums\LedgerStatus;
use Modules\Core\Exceptions\InsufficientFundsException;
use Modules\Core\ValueObjects\Money;
use Modules\Ledger\Models\LedgerEntry;
use Modules\Ledger\Services\LedgerService;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LedgerService $ledgerService;
    protected User $user1;
    protected User $user2;
    protected Account $account1;
    protected Account $account2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledgerService = $this->app->make(LedgerService::class);

        // Create test users (with name for LedgerService)
        $this->user1 = User::factory()->create(['name' => 'User One']);
        $this->user2 = User::factory()->create(['name' => 'User Two']);

        // Create test accounts (attached to users and with initial balance for testing)
        // Note: For actual scenario, balance would be updated via LedgerService
        $this->account1 = Account::factory()->create([
            'user_id' => $this->user1->id,
            'status' => AccountStatus::ACTIVE,
            'balance' => 100000, // 1000 HUB in hubits
            'hrb' => '123456789012345678901234', // Example HRB
        ]);
        $this->account2 = Account::factory()->create([
            'user_id' => $this->user2->id,
            'status' => AccountStatus::ACTIVE,
            'balance' => 0,
            'hrb' => '987654321098765432109876', // Example HRB
        ]);
    }

    /** @test */
    public function it_can_book_a_successful_internal_transfer()
    {
        $amount = Money::fromHub(100); // 100 HUB

        $this->ledgerService->bookInternalTransfer(
            $this->account1,
            $this->account2,
            $amount,
            'Test Internal Transfer'
        );

        // Assert ledger entries created
        $this->assertCount(2, LedgerEntry::all());

        $debitEntry = LedgerEntry::where('account_id', $this->account1->id)->first();
        $this->assertNotNull($debitEntry);
        $this->assertEquals($amount->getAmount(), $debitEntry->amount);
        $this->assertEquals(LedgerDirection::DEBIT, $debitEntry->direction);
        $this->assertEquals(LedgerStatus::BOOKED, $debitEntry->status);
        $this->assertEquals('Test Internal Transfer', $debitEntry->title);
        $this->assertEquals($this->user2->name, $debitEntry->counterparty_name);
        $this->assertEquals($this->account2->hrb, $debitEntry->counterparty_account);

        $creditEntry = LedgerEntry::where('account_id', $this->account2->id)->first();
        $this->assertNotNull($creditEntry);
        $this->assertEquals($amount->getAmount(), $creditEntry->amount);
        $this->assertEquals(LedgerDirection::CREDIT, $creditEntry->direction);
        $this->assertEquals(LedgerStatus::BOOKED, $creditEntry->status);
        $this->assertEquals('Test Internal Transfer', $creditEntry->title);
        $this->assertEquals($this->user1->name, $creditEntry->counterparty_name);
        $this->assertEquals($this->account1->hrb, $creditEntry->counterparty_account);
        $this->assertEquals($debitEntry->reference_id, $creditEntry->reference_id);


        // Assert cached balances updated
        $this->account1->refresh();
        $this->account2->refresh();
        $this->assertEquals(90000, $this->account1->balance); // 1000 - 100 = 900 HUB
        $this->assertEquals(10000, $this->account2->balance); // 0 + 100 = 100 HUB
    }

    /** @test */
    public function it_throws_exception_on_insufficient_funds()
    {
        $amount = Money::fromHub(1500); // More than 1000 HUB available

        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Niewystarczające środki na koncie nadawcy.');

        $this->ledgerService->bookInternalTransfer(
            $this->account1,
            $this->account2,
            $amount,
            'Insufficient Funds Test'
        );

        // Assert no ledger entries created and balances are unchanged
        $this->assertCount(0, LedgerEntry::all());
        $this->account1->refresh();
        $this->account2->refresh();
        $this->assertEquals(100000, $this->account1->balance);
        $this->assertEquals(0, $this->account2->balance);
    }

    /** @test */
    public function it_throws_exception_if_from_account_is_not_active()
    {
        $this->account1->update(['status' => AccountStatus::BLOCKED]);
        $amount = Money::fromHub(100);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Konto nadawcy nie jest aktywne.');

        $this->ledgerService->bookInternalTransfer(
            $this->account1,
            $this->account2,
            $amount,
            'Inactive Account Test'
        );

        $this->assertCount(0, LedgerEntry::all());
        $this->account1->refresh();
        $this->account2->refresh();
        $this->assertEquals(100000, $this->account1->balance);
        $this->assertEquals(0, $this->account2->balance);
    }

    /** @test */
    public function it_throws_exception_if_to_account_is_not_active()
    {
        $this->account2->update(['status' => AccountStatus::BLOCKED]);
        $amount = Money::fromHub(100);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Konto odbiorcy nie jest aktywne.');

        $this->ledgerService->bookInternalTransfer(
            $this->account1,
            $this->account2,
            $amount,
            'Inactive Account Test'
        );

        $this->assertCount(0, LedgerEntry::all());
        $this->account1->refresh();
        $this->account2->refresh();
        $this->assertEquals(100000, $this->account1->balance);
        $this->assertEquals(0, $this->account2->balance);
    }

    /** @test */
    public function it_rolls_back_all_changes_on_failure()
    {
        // Simulate a failure during the transaction
        $this->partialMock(LedgerEntry::class, function ($mock) {
            $mock->shouldReceive('create')->once()->andThrow(new \Exception('Simulated DB error'));
        });

        $amount = Money::fromHub(100);

        try {
            $this->ledgerService->bookInternalTransfer(
                $this->account1,
                $this->account2,
                $amount,
                'Rollback Test'
            );
        } catch (\Exception $e) {
            $this->assertEquals('Simulated DB error', $e->getMessage());
        }

        // Assert no ledger entries created and balances are unchanged
        $this->assertCount(0, LedgerEntry::all());
        $this->account1->refresh();
        $this->account2->refresh();
        $this->assertEquals(100000, $this->account1->balance);
        $this->assertEquals(0, $this->account2->balance);
    }
}