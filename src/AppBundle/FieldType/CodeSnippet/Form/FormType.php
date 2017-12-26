<?php

namespace AppBundle\FieldType\CodeSnippet\Form;

use AppBundle\FieldType\CodeSnippet\Type as FieldType;
use eZ\Publish\API\Repository\FieldTypeService;
use EzSystems\RepositoryForms\FieldType\DataTransformer\FieldValueTransformer;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FormType extends AbstractType
{
    /** @var FieldTypeService */
    private $fieldTypeService;

    /** @var array */
    private $availableLanguages;

    /**
     * FormType constructor.
     *
     * @param FieldTypeService $fieldTypeService
     * @param array $availableLanguages
     */
    public function __construct(FieldTypeService $fieldTypeService, array $availableLanguages)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->availableLanguages = $availableLanguages;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contents', TextareaType::class);
        $builder->add('language', ChoiceType::class, [
            'choices' => $this->availableLanguages,
            'required' => false,
        ]);
        $builder->add('firstLine', IntegerType::class, [
            'required' => false,
        ]);
        $builder->add('highlightedLines', TextType::class, [
            'required' => false,
        ]);

        $builder->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType(FieldType::FIELD_TYPE_IDENTIFIER)));
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_codesnippet';
    }
}
