<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Type;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Property;
use App\Entity\Tag;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'forms.property.name'
            ])
            ->add('description', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'basic'
                ],
                'label' => 'forms.property.description'
            ])
            ->add('surface', NumberType::class, [
                'label' => 'forms.property.surface'
            ])
            ->add('rooms', NumberType::class, [
                'label' => 'forms.property.rooms'
            ])
            ->add('bedrooms', NumberType::class, [
                'label' => 'forms.property.bedrooms'
            ])
            ->add('floor', NumberType::class, [
                'label' => 'forms.property.floor'
            ])
            ->add('price', MoneyType::class, [
                'label' => 'forms.property.price'
            ])
            ->add('heat', ChoiceType::class, [
                'choices' => $this->getChoices(),
                'label' => 'forms.property.heat'
            ])
            ->add('tags', EntityType::class, [
                'label' => 'forms.property.tags',
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false
            ])
            ->add('imageFile', FileType::class, [
                'required' => false,
                'label' => 'forms.property.image_file'
            ])
            ->add('city', TextType::class, [
                'label' => 'forms.property.city'
            ])
            ->add('address', TextType::class, [
                'label' => 'forms.property.address'
            ])

            ->add('postalCode', TextType::class, [
                'label' => 'forms.property.postal_code'
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'name',
                'label' => 'forms.property.type'
            ])

            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'forms.property.category'
            ])
        ;
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('isPublished', CheckboxType::class, [
                    'required' => false,
                    'label' => 'forms.property.is_published'
                ])
                ->add('sold', CheckboxType::class, [
                    'label' => 'forms.property.sold',
                    'required' => false
                ])
                ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }

    private function getChoices()
    {
        $choices = Property::HEAT;
        $output = [];
        foreach ($choices as $k => $v) {
            $v = strtolower($v);
            $v = $this->translator->trans("forms.property.{$v}");
            $output[$v] = $k;
        }
        return $output;
    }
}
