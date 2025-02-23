<?php

namespace App;

use FreedomtechHosting\PolydockApp\PolydockAppLoggerInterface;
use Illuminate\Console\Command;

class PolydockMiniEngineLogger implements PolydockAppLoggerInterface
{
    private Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function info(string $message, array $context = []): void
    {
        $this->command->info($message);
        $this->outputContext($context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->command->error($message);
        $this->outputContext($context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->command->warn($message);
        $this->outputContext($context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->command->comment($message);
        $this->outputContext($context);
    }

    private function outputContext(array $context): void
    {
        if (empty($context)) {
            return;
        }

        $this->command->line('Context:');
        foreach ($context as $key => $value) {
            $formattedValue = $this->formatValue($value);
            $this->command->line("  • {$key}: {$formattedValue}");
        }
    }

    private function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            return get_class($value);
        }

        return (string) $value;
    }
} 