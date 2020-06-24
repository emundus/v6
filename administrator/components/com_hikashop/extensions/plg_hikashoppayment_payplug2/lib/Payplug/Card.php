<?php
namespace Payplug;

class Card
{
    public static function delete($card, Payplug $payplug = null)
    {
        return Resource\Card::deleteCard($card, $payplug);
    }
};
