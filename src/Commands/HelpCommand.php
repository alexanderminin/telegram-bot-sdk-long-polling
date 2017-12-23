<?php

namespace Telegram\Bot\Commands;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var string Command Description
     */
    protected $description = 'Список доступных команд';

    /**
     * @var array Command Actions Description
     */
    protected $actionsDescription = [];

    /**
     * {@inheritdoc}
     */
    public function handle($action, $arguments)
    {
        $commands = $this->telegram->getCommands();
        unset($commands['last_command']);
        unset($commands['help']);

        $this->replyWithMessage(['text' =>  '*'.$this->description.'*', 'parse_mode' => 'Markdown']);

        foreach ($commands as $commandName => $handler) {
            $text = sprintf('*%s:*'.PHP_EOL.PHP_EOL.'/%s'.PHP_EOL.PHP_EOL, $handler->getDescription(), $commandName);
            $actionsDescription = $handler->getActionsDescription();
            if ($actionsDescription) {
                foreach ($handler->getActionsDescription() as $actionName => $description) {
                    $descriptionArr = explode("|", $description);
                    if (isset($descriptionArr[1])) {
                        $text .= sprintf('*%s:*'.PHP_EOL.PHP_EOL.'/%s:%s %s'.PHP_EOL.PHP_EOL, trim($descriptionArr[0]), $commandName, $actionName, trim($descriptionArr[1]));
                    } else {
                        $text .= sprintf('*%s:*'.PHP_EOL.PHP_EOL.'/%s:%s'.PHP_EOL.PHP_EOL, $description, $commandName, $actionName);
                    }
                }
            }
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'Markdown']);
        }
    }
}
