<?php

namespace PayPalHttp;

interface Injector
{
    public function inject($httpRequest);
}
