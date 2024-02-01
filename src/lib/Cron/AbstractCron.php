<?php

namespace Edgar\Cron\Cron;

use Cron\CronExpression;
use Edgar\Cron\Repository\EdgarCronRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCron.
 */
abstract class AbstractCron extends Command implements CronInterface
{
    /** @var string $minute minute expression part */
    protected $minute = '*';

    /** @var string $hour hour minute expression part */
    protected $hour = '*';

    /** @var string $dayOfMonth day of month minute expression part */
    protected $dayOfMonth = '*';

    /** @var string $month month minute expression part */
    protected $month = '*';

    /** @var string $dayOfWeek day of week minute expression part */
    protected $dayOfWeek = '*';

    /** @var array $arguments cron arguments */
    protected $arguments = [];

    /** @var int $priority cron priority */
    protected $priority = 100;

    /** @var string */
    protected $expression;

    /** @var string $alias cron alias */
    protected $alias;

    /**
     * Init Application context.
     *
     * @param Application $application
     */
    public function initApplication(Application $application)
    {
        $this->setApplication($application);
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input Input interface
     * @param OutputInterface $output Output interface
     */
    public function run(InputInterface $input, OutputInterface $output): ?int
    {
        $return = $this->execute($input, $output);
        if (null === $return) {
            $return = EdgarCronRepository::STATUS_OK;
        }

        return $return;
    }

    /**
     * Check cron expression.
     *
     * @return bool true if cron should be executed
     */
    public function isDue(): bool
    {
        $expression = $this->getExpression();
        $cron = CronExpression::factory($expression);

        return $cron->isDue();
    }

    /**
     * Return the cron expression.
     *
     * @return string cron expression
     */
    public function getExpression(): string
    {
        $expression = [
            $this->minute,
            $this->hour,
            $this->dayOfMonth,
            $this->month,
            $this->dayOfWeek,
        ];

        if (!$this->expression) {
            return implode(' ', $expression);
        }

        return $this->expression;
    }

    /**
     * Add cron arguments.
     *
     * @param string $arguments cron arguments
     */
    public function addArguments(?string $arguments = null)
    {
        $args = [];
        preg_match_all('/(?P<argument>\w+):(?P<value>[\w+\-]+)/', $arguments, $matches);
        if (isset($matches['argument'])) {
            foreach ($matches['argument'] as $key => $argKey) {
                if (isset($matches['value'][$key])) {
                    $args[$argKey] = $matches['value'][$key];
                }
            }
        }
        $this->arguments = $args;
    }

    /**
     * List arguments.
     *
     * @return string
     */
    public function getArguments(): string
    {
        $arguments = [];
        foreach ($this->arguments as $key => $val) {
            $arguments[] = $key . ':' . $val;
        }

        return implode(' ', $arguments);
    }

    /**
     * Set Cron priority.
     *
     * @param int $priority
     */
    public function addPriority(int $priority)
    {
        $this->priority = $priority;
    }

    /**
     * Set Cron expression.
     *
     * @param string $expression
     */
    public function addExpression(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Get Cron priority.
     *
     * @return int cron priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get Cron arguments.
     *
     * @param InputInterface $input Input interface
     * @param string $key
     *
     * @return string|null return argument from input or tag settings
     */
    public function getArgument(InputInterface $input, $key): ?string
    {
        if ($input->hasArgument($key)) {
            return $input->getArgument($key);
        }
        if (isset($this->arguments[$key])) {
            return $this->arguments[$key];
        }

        return null;
    }

    /**
     * Set cron alias.
     *
     * @param string $alias cron alias
     */
    public function setAlias(string $alias)
    {
        $this->alias = $alias;
    }

    /**
     * Get cron alias.
     *
     * @return string cron alias
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}
