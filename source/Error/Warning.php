<?php

declare(strict_types=1);

namespace Sage\Error;

use function trigger_error;
use const E_USER_WARNING;

/**
 * Encapsulates warnings produced by the library.
 *
 * Warnings can be suppressed (individually or all) if required.
 * Also it is possible to override warning handler (which is **trigger_error()** by default)
 */
final class Warning
{
  public const ASSIGN             = 2;
  public const CONFIG             = 4;
  public const FULL_SCHEMA_SCAN   = 8;
  public const CONFIG_DEPRECATION = 16;
  public const NOT_A_TYPE         = 32;
  public const ALL                = 63;

  /** @var int */
  private static $enableWarnings = self::ALL;

  /** @var mixed[] */
  private static $warned = [];

  /** @var callable|null */
  private static $warningHandler;

  /**
   * Sets warning handler which can intercept all system warnings.
   * When not set, trigger_error() is used to notify about warnings.
   *
   * @api
   */
  public static function setWarningHandler(?callable $warningHandler = null): void
  {
    self::$warningHandler = $warningHandler;
  }

  public static function warnOnce(string $errorMessage, int $warningId, ?int $messageLevel = null): void
  {
    $messageLevel = $messageLevel ?? E_USER_WARNING;

    if (self::$warningHandler !== null) {
      $fn = self::$warningHandler;
      $fn($errorMessage, $warningId, $messageLevel);
    } elseif ((self::$enableWarnings & $warningId) > 0 && !isset(self::$warned[$warningId])) {
      self::$warned[$warningId] = true;
      trigger_error($errorMessage, $messageLevel);
    }
  }

  public static function warn(string $errorMessage, int $warningId, ?int $messageLevel = null): void
  {
    $messageLevel = $messageLevel ?? E_USER_WARNING;

    if (self::$warningHandler !== null) {
      $fn = self::$warningHandler;
      $fn($errorMessage, $warningId, $messageLevel);
    } elseif ((self::$enableWarnings & $warningId) > 0) {
      trigger_error($errorMessage, $messageLevel);
    }
  }
}
