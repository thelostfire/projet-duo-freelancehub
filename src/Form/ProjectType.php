<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Project;
use App\Repository\ClientRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{

    public function __construct(private Security $security)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('statut')
            ->add('Client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'name',
                'query_builder' => fn (ClientRepository $repo) => $repo->createQueryBuilder('c')
                    ->andWhere('c.owner = :owner')
                    ->setParameter('owner', $this->security->getUser())
                    ->orderBy('c.name', 'ASC')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
