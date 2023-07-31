<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        try {
            $discount = $this->apiService->createDiscountCode($merchant);

            $affiliate = new Affiliate([
                'commission_rate' => $commissionRate,
                'discount_code' => $discount['code']
            ]);

            $new_user = new User([
                "email" => $email,
                "name" => $name,
                "type" => User::TYPE_AFFILIATE,
            ]);
//            dd($merchant);

//            $new_user->merchant()->associate($merchant);
            $new_user->affiliate()->save($affiliate);

            Mail::to($new_user->email)->send(new AffiliateCreated($affiliate));

            return $affiliate;
        }
        catch(\Exception $exception) {
            throw new AffiliateCreateException("Error creating affiliate: {$exception->getMessage()}");
        }
    }
}
