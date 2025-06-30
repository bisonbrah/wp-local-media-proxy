<?php

namespace LocalMediaProxy\Core;

/**
 * Class Loader
 *
 * Responsible for managing the registration of actions and filters in WordPress.
 */
class Loader
{
    /**
     * The array of actions registered with WordPress.
     *
     * @var array $actions The actions registered with WordPress to fire when the plugin loads.
     */
    protected array $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @var array $filters The filters registered with WordPress to fire when the plugin loads.
     */
    protected array $filters;

    /**
     * Constructor for initializing actions and filters arrays.
     *
     * @return void
     */
    public function __construct()
    {
        $this->actions = [];
        $this->filters = [];
    }

    /**
     * Adds a WordPress action to the internal actions array.
     * Ref: https://developer.wordpress.org/reference/functions/add_filter/
     *
     * @param string $hook The name of the WordPress action that is being registered.
     * @param mixed $component A reference to the instance of the class on which the callback is defined.
     * @param string $callback The name of the callback function to be executed when the action is fired.
     * @param int $priority Optional. The priority at which the action should be fired. Default is 10.
     * @param int $accepted_args Optional. The number of arguments that should be passed to the callback. Default is 1.
     * @return void
     */
    public function add_action(
        string $hook,
        object  $component,
        string $callback,
        int    $priority = 10,
        int    $accepted_args = 1
    ): void
    {
        $this->actions = $this->add(
            $this->actions,
            $hook,
            $component,
            $callback,
            $priority,
            $accepted_args
        );
    }

    /**
     * Adds a filter to the list of filters to be registered with WordPress.
     * Ref: https://developer.wordpress.org/reference/functions/add_action/
     *
     * @param string $hook The name of the WordPress filter to be registered.
     * @param object $component The instance of the object on which the filter is defined.
     * @param string $callback The name of the method to be called when the filter is triggered.
     * @param int $priority Optional. The priority at which the filter should be executed. Default is 10.
     * @param int $accepted_args Optional. The number of arguments accepted by the filter callback. Default is 1.
     *
     * @return void
     */
    public function add_filter(
        string $hook,
        object $component,
        string $callback,
        int    $priority = 10,
        int    $accepted_args = 1
    ): void
    {
        $this->filters = $this->add(
            $this->filters,
            $hook,
            $component,
            $callback,
            $priority,
            $accepted_args
        );
    }

    /**
     * Adds a new hook configuration to the provided hooks array.
     * Ref: https://developer.wordpress.org/plugins/hooks/custom-hooks/
     *
     * @param array $hooks The array of existing hooks to which the new hook will be added.
     * @param string $hook The name of the hook to be registered.
     * @param object $component The component associated with the callback.
     * @param string $callback The name of the callback function/method to be executed.
     * @param int $priority The priority at which the hook callback function is executed.
     * @param int $accepted_args The number of arguments accepted by the callback function.
     *
     * @return array The updated array of hooks containing the new hook configuration.
     */
    private function add(
        array  $hooks,
        string $hook,
        object $component,
        string $callback,
        int    $priority,
        int    $accepted_args
    ): array
    {
        $hooks[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        return $hooks;
    }

    /**
     * Registers all the defined filters and actions with WordPress.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                [$hook['component'], $hook['callback']],
                $hook['priority'],
                $hook['accepted_args']
            );
        }
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                [$hook['component'], $hook['callback']],
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
