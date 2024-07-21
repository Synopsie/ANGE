<?php
declare(strict_types=1);

namespace synopsie\events;

use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\Listener;
use pocketmine\event\RegisteredListener;
use pocketmine\timings\Timings;
use pocketmine\utils\Utils;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use synopsie\Engine;

class ListenerManager {

    private function getEventsHandledBy(ReflectionMethod $method) : ?string {
        try {
            if ($method->isStatic() || !$method->getDeclaringClass()->implementsInterface(Listener::class)) {
                return null;
            }
            $parameters = $method->getParameters();
            if (count($parameters) !== 1) {
                return null;
            }
            $paramType = $parameters[0]->getType();
            if (!$paramType instanceof ReflectionNamedType || $paramType->isBuiltin()) {
                return null;
            }

            $paramClass = $paramType->getName();
            $eventClass = new ReflectionClass($paramClass);
            if (!$eventClass->isSubclassOf(Event::class)) {
                return null;
            }
            return $eventClass->getName();
        } catch (ReflectionException $e) {
            Engine::getInstance()->getLogger()->warning("ReflectionException: " . $e->getMessage());
        }
        return null;
    }

    /**
     * @throws ReflectionException
     */
    public function registerListener(EngineListener|Listener $event) : ?RegisteredListener {
        $reflection = new ReflectionClass(get_class($event));
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $handlerClosure   = $method->getClosure($event);
            $engine           = Engine::getInstance();
            $registerListener = new RegisteredListener(
                $handlerClosure,
                EventPriority::NORMAL,
                $engine,
                false,
                Timings::getEventHandlerTimings(
                    Event::class,
                    Utils::getNiceClosureName($handlerClosure),
                    $engine->getDescription()->getFullName()
                )
            );
            HandlerListManager::global()->getListFor($this->getEventsHandledBy($method))->register($registerListener);
        }
        return $registerListener ?? null;
    }

    public function registerListeners(EngineListener|Listener ...$listeners) : void {
        foreach ($listeners as $listener) {
            $this->registerListener($listener);
        }
    }
}