<?php


namespace API;


class ApiObject
{
    public function __construct(string $resourceName, ControllerInterface $controller, ApiRouter $router)
    {
        $router->add('/^\/(' . $resourceName . ')$/', 'GET', $controller,
            function (ApiRequest $request, ApiResponse $response, ControllerInterface $controller) {
                if ((PaginationValidator::getInstance($_GET['page'], $_GET['per_page'], $response)->validate())) {
                    $controller->printList($_GET['page'], $_GET['per_page']);
                }
            });

        $router->add('/^\/(' . $resourceName . ')\/[0-9]+$/', 'GET', $controller,
            function (ApiRequest $request, ApiResponse $response, ControllerInterface $controller) {
                $id = intval(explode('/', $request->getPath())[2]);
                $controller->print($id);
            });

        $router->add('/^\/(' . $resourceName . ')\/[0-9]+$/', 'PUT', $controller,
            function (ApiRequest $request, ApiResponse $response, ControllerInterface $controller) {
                $id = intval(explode('/', $request->getPath())[2]);
                parse_str(file_get_contents('php://input'), $_PUT);
                $controller->update($id, $_PUT);
            });

        $router->add('/^\/(' . $resourceName . ')\/[0-9]+$/', 'DELETE', $controller,
            function (ApiRequest $request, ApiResponse $response, ControllerInterface $controller) {
                $id = intval(explode('/', $request->getPath())[2]);
                $controller->delete($id);
            });

        $router->add('/^\/(' . $resourceName . ')\/$/', 'POST', $controller,
            function (ApiRequest $request, ApiResponse $response, ControllerInterface $controller) {
                $controller->add($_POST);
            });
    }
}