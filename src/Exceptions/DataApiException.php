<?php

namespace AtLab\Comagic\Exceptions;

class DataApiException extends \Exception
{
    private array $errorsArr;

    public function __construct($context, $responseBody)
    {
        \Log::debug(json_encode($responseBody));
        $this->errorsArr = [
            //Общие ошибки
            ['code' => -32600, 'mnemo' => 'invalid_request', 'description' => 'Ошибки связанные с валидацией параметров запроса - id, jsonrpc'],
            ['code' => -32001, 'mnemo' => 'access_token_expired', 'description' => 'Применяется только к постоянному токену. Если время жизни постоянного токена истекло, то возвращается указанная ошибка'],
            ['code' => -32001, 'mnemo' => 'access_token_blocked', 'description' => 'Если постоянный токен заблокирован, то возвращается указанная ошибка'],
            ['code' => -32001, 'mnemo' => 'access_token_invalid', 'description' => 'Указанная ошибка возвращается если постоянный/временный токен не найден'],
            ['code' => -32029, 'mnemo' => 'limit_exceeded', 'description' => 'Лимит превышен'],
            ['code' => -32008, 'mnemo' => 'method_component_disabled', 'description' => 'Не подключен компонент, который требуется для работы метода'],
            ['code' => -32008, 'mnemo' => 'parameter_component_disabled', 'description' => 'Не подключен компонент, который нужен для заполнения параметра и создания сущности'],
            ['code' => -32003, 'mnemo' => 'ip_not_whitelisted', 'description' => 'IP адрес с которого делается запрос не находится в белом списке адресов.'],
            ['code' => -32001, 'mnemo' => 'auth_error', 'description' => 'Неправильный логин или пароль'],
            ['code' => -32009, 'mnemo' => 'account_inactive', 'description' => 'Аккаунт заблокирован'],
            ['code' => -32603, 'mnemo' => 'internal_error', 'description' => 'Внутренняя ошибка, необходимо обратиться в службу технической поддержки'],
            ['code' => -32602, 'mnemo' => 'data_type_error', 'description' => 'Ошибка в типе передаваемых данных'],
            ['code' => -32601, 'mnemo' => 'method_not_found', 'description' => 'Вызываемый метод не найден'],
            ['code' => -32003, 'mnemo' => 'forbidden', 'description' => 'Нет прав на доступ к методу или API, или запрещено выполнять какое-либо действие'],
            ['code' => -32700, 'mnemo' => 'parse_error', 'description' => 'Ошибка валидации JSON'],
            ['code' => -32099, 'mnemo' => 'batch_opreations_not_supported', 'description' => 'Групповые операции не поддерживаются'],
            ['code' => -32099, 'mnemo' => 'notifications_not_supported', 'description' => 'Был потерян параметр id в запросе.'],
            ['code' => -32602, 'mnemo' => 'required_parameter_missed', 'description' => 'Обязательный параметр не передали'],
            ['code' => -32602, 'mnemo' => 'invalid_parameter_value', 'description' => 'Возвращается во всех случаях, если было передано некорректное значение параметра или переданное значение не соответствует требуемому формату ввода'],
            ['code' => -32602, 'mnemo' => 'unexpected_parameters', 'description' => 'Если в "params" были переданы параметры которые не предусмотрены JSON структурой метода или указан параметр для сортировки, фильтрации и выборки, который не существует'],
            ['code' => -32602, 'mnemo' => 'invalid_parameters_combination', 'description' => 'Если параметры указанные в методе находятся в недопустимой комбинации или имеют зависмость друг от друга. Нужно смотреть документацию по методу и его параметрам.'],

            //Список ошибок для методов с глаголом get
            ['code' => -32602, 'context' => 'get', 'mnemo' => 'sort_prohibited', 'Сортировка по параметру запрещена и невозможна, так как параметр для сортировки не находится в списке разрешенных для сортировки'],
            ['code' => -32602, 'context' => 'get', 'mnemo' => 'filter_prohibited', 'Фильтрация по параметру запрещена и невозможна, так как параметр для фильтрации не находится в списке разрешенных для фильтрации'],
            ['code' => -32602, 'context' => 'get', 'mnemo' => 'date_interval_limit_reached', 'Если в запросе период между указанными датами в date_from и date_till превышает 3 месяца. В основном ошибка актуальна только для методов получения отчетов, но не для всех.'],

            ['code' => -32602, 'context' => 'delete', 'mnemo' => 'date_interval_limit_reached', 'Если в запросе период между указанными датами в date_from и date_till превышает 3 месяца. В основном ошибка актуальна только для методов получения отчетов, но не для всех.']


        ];

        $error_description = $this->getDescription($context, $responseBody);
        $message = "
        ОШИБКА ОТПРАВКИ ДАННЫХ В COMAGIC
        Ошбика: {$responseBody->error->message};
        Код ошибки: {$responseBody->error->code};
        Поле: {$responseBody->error->data->field};
        Значение: {$responseBody->error->data->value}
        Описание: $error_description
        ";
        parent::__construct($message, $responseBody->error->code);
    }

    private function getDescription($context, $responseBody)
    {
        foreach ($this->errorsArr as $error) {
            if ($error['code'] === $responseBody->error->code && $error['mnemo'] === $responseBody->error->data->mnemonic) {
                if (array_key_exists('context', $error) && $error["context"] === $context) {
                    $errorContexDesc = $error['description'];
                    break;
                } else {
                    $errorDesc = $error['description'];
                }
            }
        }
        return $errorContexDesc ?? $errorDesc ?? 'Dynamic Error';
    }
}
