<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Carbon::setLocale('en');
        date_default_timezone_set('Asia/Manila');

        // Fix for shared hosting MAX_JOIN_SIZE limitation
        // Allows complex queries with multiple JOINs to run without hitting row limits
        try {
            \Illuminate\Support\Facades\DB::statement('SET SQL_BIG_SELECTS=1');
        } catch (\Exception $e) {
            // Silent fail if DB not yet available (e.g. during migrations)
        }

        // Global Notifications for Franchise Expirations
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                $expiringFranchise = [];
                $cases = \Illuminate\Support\Facades\DB::table('franchise_cases')
                    ->whereNull('deleted_at')
                    ->whereNotNull('expiry_date')
                    ->get();
                
                $today = Carbon::today();
                $nextYear = Carbon::today()->addYear();
                
                foreach ($cases as $c) {
                    $expDt = Carbon::parse($c->expiry_date);
                    if ($expDt->isPast()) {
                        $expiringFranchise[] = [
                            'type' => 'case_expiry',
                            'title' => 'Expired Franchise Alert',
                            'message' => 'Case No. ' . $c->case_no . ' (' . $c->applicant_name . ') has already expired on ' . $expDt->format('M d, Y') . '.',
                            'url' => route('decision-management.index')
                        ];
                    } elseif ($expDt->isBetween($today, $nextYear)) {
                        $expiringFranchise[] = [
                            'type' => 'case_expiry',
                            'title' => '1-Year Renewal Alert',
                            'message' => 'Case No. ' . $c->case_no . ' (' . $c->applicant_name . ') is due for renewal under a year (' . $expDt->format('M d, Y') . ').',
                            'url' => route('decision-management.index')
                        ];
                    }
                }
                
                $view->with('expiringFranchise', $expiringFranchise);
            } catch (\Exception $e) {
                // If DB is missing during initial setup, silently ignore
            }
        });
    }
}
