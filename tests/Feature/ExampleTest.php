<?php

it('renders the public tenant homepage without a 500', function () {
    // Resolves to the baseline institute (status = 1) via InstituteHelper's
    // fallback. Regression guard for the theme partials that read `$settings`
    // keys which may not exist for a freshly created institute.
    $this->get('/')->assertOk();
});
