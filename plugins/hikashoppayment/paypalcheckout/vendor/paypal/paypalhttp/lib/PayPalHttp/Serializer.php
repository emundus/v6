<?php

namespace PayPalHttp;

interface Serializer
{
    public function contentType();

    public function encode(HttpRequest $request);

    public function decode($body);
}
