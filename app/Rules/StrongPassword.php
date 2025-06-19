<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class StrongPassword implements ValidationRule
{
    /**
     * Minimum password length
     *
     * @var int
     */
    protected $minLength = 8;

    /**
     * Whether to check for mixed case
     *
     * @var bool
     */
    protected $requireMixedCase = true;

    /**
     * Whether to check for numbers
     *
     * @var bool
     */
    protected $requireNumbers = true;

    /**
     * Whether to check for symbols
     *
     * @var bool
     */
    protected $requireSymbols = true;

    /**
     * Whether to check for breached passwords
     *
     * @var bool
     */
    protected $checkBreached = true;

    /**
     * Create a new rule instance.
     *
     * @param int $minLength
     * @param bool $requireMixedCase
     * @param bool $requireNumbers
     * @param bool $requireSymbols
     * @param bool $checkBreached
     */
    public function __construct(
        int $minLength = 8,
        bool $requireMixedCase = true,
        bool $requireNumbers = true,
        bool $requireSymbols = true,
        bool $checkBreached = true
    ) {
        $this->minLength = $minLength;
        $this->requireMixedCase = $requireMixedCase;
        $this->requireNumbers = $requireNumbers;
        $this->requireSymbols = $requireSymbols;
        $this->checkBreached = $checkBreached;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check length
        if (strlen($value) < $this->minLength) {
            $fail("The {$attribute} must be at least {$this->minLength} characters.");
            return;
        }

        // Check for mixed case
        if ($this->requireMixedCase && !preg_match('/[a-z]/', $value)) {
            $fail("The {$attribute} must contain at least one lowercase letter.");
            return;
        }

        if ($this->requireMixedCase && !preg_match('/[A-Z]/', $value)) {
            $fail("The {$attribute} must contain at least one uppercase letter.");
            return;
        }

        // Check for numbers
        if ($this->requireNumbers && !preg_match('/[0-9]/', $value)) {
            $fail("The {$attribute} must contain at least one number.");
            return;
        }

        // Check for symbols
        if ($this->requireSymbols && !preg_match('/[^a-zA-Z0-9]/', $value)) {
            $fail("The {$attribute} must contain at least one symbol.");
            return;
        }

        // Check for breached passwords
        if ($this->checkBreached && $this->isBreached($value)) {
            $fail("The {$attribute} has been found in a data breach. Please choose a different password.");
            return;
        }
    }

    /**
     * Check if the password has been breached using the Have I Been Pwned API.
     *
     * @param string $password
     * @return bool
     */
    protected function isBreached(string $password): bool
    {
        try {
            // Generate SHA-1 hash of the password
            $hash = strtoupper(sha1($password));
            $prefix = substr($hash, 0, 5);
            $suffix = substr($hash, 5);

            // Query the Have I Been Pwned API
            $response = Http::get("https://api.pwnedpasswords.com/range/{$prefix}");

            if ($response->successful()) {
                $hashes = explode("\r\n", $response->body());

                foreach ($hashes as $line) {
                    [$hashSuffix, $count] = explode(':', $line);

                    if ($hashSuffix === $suffix) {
                        return true; // Password has been breached
                    }
                }
            }

            return false; // Password has not been breached
        } catch (\Exception $e) {
            // If the API call fails, we'll assume the password is not breached
            // but log the error for monitoring
            \Log::error("Password breach check failed: " . $e->getMessage());
            return false;
        }
    }
}
