<?php

declare(strict_types=1);

namespace Sage\Error;

use RuntimeException;

/**
 * Error caused by actions of Sage clients. Can be safely displayed to a client...
 */
class UserError extends RuntimeException implements ClientAware
{
  /**
   * @return bool
   */
  public function isClientSafe()
  {
    return true;
  }

  /**
   * @return string
   */
  public function category()
  {
    return 'user';
  }
}
