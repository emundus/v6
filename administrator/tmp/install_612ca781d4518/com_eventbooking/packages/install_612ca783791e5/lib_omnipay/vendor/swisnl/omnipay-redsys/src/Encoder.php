<?php

namespace Omnipay\RedSys;

final class Encoder
{
    public function encode(array $input)
    {
        $output = json_encode($input);
        $output = base64_encode($output);
        return $output;
    }

    public function decode($input)
    {
        $output = base64_decode($input);
        $output = json_decode($output, true);
        return $output;
    }
}
