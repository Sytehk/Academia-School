<?php

return [
    'name' => 'Ai',

    // Default Claude model + generation settings used by ClaudeService.
    // These are conversational/summarization tasks, not agentic coding, so a
    // mid-tier effort level is the right default — see ClaudeService.
    'model' => 'claude-opus-5',
    'max_tokens' => 4096,
    'effort' => 'medium',
];
