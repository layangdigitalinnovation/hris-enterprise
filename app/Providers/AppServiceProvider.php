<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Helpers\CurrencyHelper;
use App\Services\ApprovalNotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.tailwind');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.tailwind');

        // Blade Directives for Currency
        Blade::directive('currency', function ($expression) {
            return "<?php echo \\App\\Helpers\\CurrencyHelper::format($expression); ?>";
        });

        Blade::directive('currencySymbol', function ($expression) {
            return "<?php echo \\App\\Helpers\\CurrencyHelper::symbol($expression); ?>";
        });

        // View Composer for Top Navbar Approval Notifications Bell
        View::composer('layouts.partials.notification_bell', function ($view) {
            if (auth()->check()) {
                $service = app(ApprovalNotificationService::class);
                $view->with('approvalNotificationSummary', $service->getSummary(auth()->user()));
            } else {
                $view->with('approvalNotificationSummary', [
                    'total_count' => 0,
                    'counts' => [
                        'leave' => 0,
                        'overtime' => 0,
                        'adjustment' => 0,
                        'reimbursement' => 0,
                        'loan' => 0,
                        'resignation' => 0,
                        'transition' => 0,
                    ],
                    'items' => [],
                ]);
            }
        });
    }
}
