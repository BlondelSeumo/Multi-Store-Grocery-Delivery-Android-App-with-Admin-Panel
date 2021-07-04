# Multi-Store-Grocery-Delivery-Android-App-with-Admin-Panel
Customer Android App Source Code Store Manager Android Source Code App Delivery Boy Android App Source Code Admin Panel PHP Script Source Code Database file &amp; Installation Documentation

#Very Important :

To remove The purchase code

laravel_application/vendor/smartervision/laravel-installer/src/Controllers/Envi
r
onmentController.php

And remove these lines (line 110):

if (isset($errors) || !$code){
$redirect->route('LaravelInstaller::environmentClassic')
->with(['message' => $message,'errors'=>$errors]);
//return view('vendor.installer.environment-classic', compact('errors'));
}


And these (line 185):

if (isset($errors) || !$code){
return view('vendor.installer.environment-wizard', compact('errors'));
}


And change this URL to anything:

https://api.envato.com/v3/market/author/sale?code={$code}

to

https://domain.com/v3/market/author/sale?code={$code}


Use any 36 characters when installing
