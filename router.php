<?php

/**
 * Класс маршрутизатора для обработки запросов и выполнения соответствующих действий.
 */
class Router {

    private array $list = [];          // Список зарегистрированных путей и классов
    private Model $currentModel;       // Текущий класс модели для обработки запроса
    private array $absolutePath = [];  // Путь, переданный в запросе

    /**
     * Конструктор маршрутизатора.
     * Обрезает первый элемент из абсолютного пути.
     *
     * @param array $absolutePath Абсолютный путь из запроса
     */
    public function __construct(array $absolutePath)
    {
        $this->absolutePath = array_splice($absolutePath, 1);  // Убираем первый элемент (обычно это 'api')
    }

    /**
     * Регистрирует класс модели и путь.
     *
     * @param Model $class Класс модели
     * @param string $path Путь для маршрута
     * @return void
     */
    public function register(Model $class, string $path): void {
        $this->list[] = [
            'class' => $class,  // Класс модели
            'path'  => $path    // Путь, по которому доступна модель
        ];
    }

    /**
     * Возвращает список зарегистрированных путей и моделей.
     *
     * @return array Список зарегистрированных маршрутов
     */
    public function getList(): array {
        return $this->list;
    }

    /**
     * Проверяет, существует ли путь в зарегистрированных маршрутах.
     * Если путь найден, сохраняет соответствующую модель для дальнейшего выполнения.
     *
     * @param string $path Путь для проверки
     * @return bool True, если путь найден, иначе false
     */
    public function isPath(string $path): bool {
        if (empty($path)) {
            throw new \InvalidArgumentException('Путь не может быть пустым');
        }
    
        foreach ($this->list as $item) {
            if ($item['path'] === $path) {
                $this->currentModel = $item['class'];
                return true;
            }
        }
        return false;
    }
    
    /**
     * Выполняет метод выбранной модели.
     * Перед выполнением метода, задает путь для модели.
     */
    public function execute(): void {
        $this->currentModel->setPath($this->absolutePath);  // Устанавливаем путь в модель
        try {
            $this->currentModel->execute();  // Выполняем метод модели
        } catch (Exception $e) {  
            json([
                'error' => $e->getMessage()  // Сообщение об ошибке
            ], 200);
        }
    }
}

?>
