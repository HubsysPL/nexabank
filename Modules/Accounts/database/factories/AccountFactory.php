<?php

namespace Modules\Accounts\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Models\Account;
use Modules\Core\Enums\AccountStatus;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $user = User::factory()->create(); // Ensure a user is created
        
        // Placeholder for HRB generation, actual HRB service would be used here
        $hrb = 'HRB' . $this->faker->unique()->numerify('#####################'); // 24 chars

        return [
            'user_id' => $user->id,
            'product_code' => 'STANDARD', // Assuming 'STANDARD' product exists
            'hrb' => $hrb,
            'bank_code' => 'NEXA',
            'institution_code' => '0000',
            'account_sequence' => $this->faker->unique()->numerify('############'), // 12 digits
            'status' => AccountStatus::ACTIVE,
            'balance' => 0,
        ];
    }
}