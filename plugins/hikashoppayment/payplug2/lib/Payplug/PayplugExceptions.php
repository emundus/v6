<?php

class PayplugException extends Exception
{
}

class InvalidCredentialsException extends PayplugException
{
}

class InvalidSignatureException extends PayplugException
{
}

class MalformedURLException extends PayplugException
{
}

class NetworkException extends PayplugException
{
}

class ParametersNotSetException extends PayplugException
{
}

class MissingRequiredParameterException extends PayplugException
{
}

