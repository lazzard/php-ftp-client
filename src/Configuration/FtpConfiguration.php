<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException;

/**
 * {@inheritDoc}
 */
class FtpConfiguration extends FtpConfigurationOptions
{
    /**
     * FtpConfiguration constructor.
     *
     * @param array|null $options
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException
     */
    public function __construct($options = null)
    {
        if (is_null($options) === false) {
            foreach ($options as $option => $value)
            {
                # Get current object vars as an array in insensitive format
                $object_vars_lower_case = array_change_key_case(get_object_vars($this), CASE_LOWER);
                # Check if option is exists
                if (key_exists(strtolower($option), $object_vars_lower_case)) {
                    # Validate option
                    if (OptionsContracts::isValidateOption([$option => $value]) === true) {
                        # Call setter
                        $call_func = "set" . ucfirst($option);
                        $this->$call_func($value);
                    }
                } else {
                    # Invalid configuration option
                    throw FtpConfigurationOptionException::invalidConfigurationOption($option);
                }
            }
        }
    }

}