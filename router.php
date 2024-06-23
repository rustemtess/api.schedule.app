<?php

class Router {

    private array $list = [];
    private Model $currentModel;
    private array $absolutePath = [];

    public function __construct(array $absolutePath)
    {
        $this->absolutePath = array_splice($absolutePath, 1);
    }

    /**
     * Регистрация модуля и путь
     * @param object $class
     * @param string $path
     * @return void
     */
    public function register(Model $class, string $path): void {
        $this->list[] = [
            'class' => $class,
            'path' => $path
        ];
    }

    /**
     * Получить список зарегистрированных путей
     * @return array
     */
    public function getList(): array {
        return $this->list;
    }

    /**
     * Проверка пути
     * @param string $path
     * @return bool
     */
    public function isPath(string $path): bool {
        foreach($this->list as $item) {
            if($item['path'] === $path) {
                $this->currentModel = $item['class'];
                return true;
            }
        }
        return false;
    }

    public function execute(): void {
        $this->currentModel->setPath($this->absolutePath);
        try {
            $this->currentModel->execute();
        }catch(Exception $e) {
            json([
                'error' => $e->getMessage()
            ]);
        }
    }

}

?>