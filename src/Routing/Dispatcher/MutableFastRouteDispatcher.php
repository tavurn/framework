<?php

namespace Tavurn\Routing\Dispatcher;

trait MutableFastRouteDispatcher
{
    public function updateData(array $data): void
    {
        [$staticRouteMap, $variableRouteData] = $data;

        $this->setStaticRouteMap($staticRouteMap);

        $this->setVariableRouteData($variableRouteData);
    }

    public function setStaticRouteMap(array $data): void
    {
        $this->staticRouteMap = $data;
    }

    public function setVariableRouteData(array $data): void
    {
        $this->variableRouteData = $data;
    }
}
