<?php

/**
 * Levels of password security
 */
class PasswordSecureLevel
{
    // not secure at all - should not be accepted
    const NOT_SECURE = 0;
    // minimal security (one character class only)
    const MINIMAL = 1;
    // normal (two to three classes)
    const NORMAL = 2;
    // probably more secure password than any other (four character classes)
    const SECURE = 3;
}
