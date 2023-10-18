<?php
namespace Payplug\Core;

interface IHttpRequest
{
    function setopt($option, $value);

    function exec();

    function getinfo($option);

    function close();

    function error();

    function errno();
}
