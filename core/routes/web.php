<?php
 
use Illuminate\Support\Facades\Route;
 
 
Route::middleware('cron.secret')->group(function () {
    Route::get('cron', 'CronController@cron')->name('cron');
    Route::get('stageOne', 'CronController@stageOneComplete')->name('stageOneComplete');
    Route::get('stageOut', 'CronController@stageOut')->name('stageOut');

    // reconnect users to children in previous stages
    Route::get('recStart/{stage}', 'CronController@recFire')->name('recStart');
    Route::get('recon/{stage}', 'CronController@reconnectImmediate')->name('reconnectImme');
    // reconnect all downlines in a stage (active + inactive)
    Route::get('reconAll/{stage}', 'CronController@reconnectAll')->name('reconAll');
    // dispatch award payment processing jobs for all eligible users
    Route::get('paymentsDispatch', 'CronController@paymentsDispatch')->name('paymentsDispatch');
    // check and process award qualifications
    Route::get('awardCheck', 'CronController@awardCheck')->name('awardCheck');
    // retry missed ACB upline bonus payouts
    Route::get('acbProcess', 'CronController@acbProcess')->name('acbProcess');
    // dump database to storage/app/db_bk/ as a timestamped gzip-compressed SQL file
    Route::get('dbBackup', 'CronController@dbBackup')->name('dbBackup');
    // dispatch matching-bonus jobs for all eligible user matrices
    Route::get('matchingDispatch', 'CronController@matchingDispatch')->name('matchingDispatch');
    // sweep unclaimed autoship balances to admin (guarded internally to last day/hour of month)
    Route::get('autoshipSweep', 'CronController@autoshipSweep')->name('autoshipSweep');
    // clear application cache (config/route/view/application) after a deploy
    Route::get('clearCache', 'CronController@clearCache')->name('clearCache');
});
 

// User Support Ticket

Route::middleware(['auth', 'XssSanitizer'])->group(function () {
    Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group( function () { 
        Route::get('/', 'supportTicket')->name('index');
        Route::get('new', 'openSupportTicket')->name('open');
        Route::post('create', 'storeSupportTicket')->name('store');
        Route::get('view/{ticket}', 'viewTicket')->name('view');
        Route::post('reply/{id}', 'replyTicket')->name('reply');
        Route::post('close/{id}', 'closeTicket')->name('close');
        Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
    });
});

Route::controller('SiteController')->group( function () {
    
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');

    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    Route::get('agreement', 'aggreement')->name('agreement');

    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::middleware(['auth', 'XssSanitizer'])->group(function () {
        Route::get('/products/{catId?}', 'products')->name('products');
        Route::get('/product/{id}/{slug}', 'productDetails')->name('product.details');
        Route::get('/products1/{catId?}', 'products1')->name('products1');
        Route::get('/product1/{id}/{slug}', 'productDetails1')->name('product.details1');
    });

    Route::post('/check/referral', 'checkUsername')->name('check.referral');
    Route::post('/check/matrixParent', 'checkParent')->name('check.parentMatrix');
    Route::post('/get/user/position', 'userPosition')->name('get.user.position');

    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home'); 
});
