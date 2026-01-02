<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Routing;

final class ResourceOptions
{
    /**
     * @var self
     */
    private static self $default;

    /**
     * @var self
     */
    private static self $readOnly;

    /**
     * @var self
     */
    private static self $noDelete;

    /**
     * @return self
     */
    public static function default(): self
    {
        return self::$default ??= new self(
            viewable: true,
            creatable: true,
            editable: true,
            deletable: true,
        );
    }

    /**
     * @return self
     */
    public static function readOnly(): self
    {
        return self::$readOnly ??= new self(
            viewable: true,
            creatable: false,
            editable: false,
            deletable: false,
        );
    }

    /**
     * @return self
     */
    public static function noDelete(): self
    {
        return self::$noDelete ??= new self(
            viewable: true,
            creatable: true,
            editable: true,
            deletable: false,
        );
    }

    /**
     * @param bool $creatable
     * @param bool $editable
     * @param bool $deletable
     * @param bool $viewable
     * @param bool $form
     */
    public function __construct(
        public bool $viewable = true,
        public bool $creatable = true,
        public bool $editable = true,
        public bool $deletable = true,
        public bool $form = false,
    ) {
    }

    public function useForm(): self
    {
        $this->form = true;
        return $this;
    }
}
