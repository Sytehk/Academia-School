<?php

namespace Modules\Examination\Exceptions;

use RuntimeException;

/**
 * A business-rule refusal in the CBT workflow (exam not open, attempt limit
 * reached, attempt already submitted, ...). Controllers turn it into a 422 with
 * the message shown to the user; it is never a server error.
 */
class OnlineExamException extends RuntimeException {}
