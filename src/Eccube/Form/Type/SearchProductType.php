<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class SearchProductType extends AbstractType
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Category list
        $Categories = $this->app['eccube.repository.category']->getList();
        
        $builder->add('mode', 'hidden', array(
            'data' => 'search',
        ));
        $builder->add('category_id', 'category', array(
            'empty_value' => '全ての商品',
            'empty_data' => null,
            'required' => false,
            'label' => '商品カテゴリから選ぶ',
            // Choices list (overdrive mapped)
            'choices' => $this->getCategoryChoice($Categories)
        ));
        $builder->add('name', 'search', array(
            'required' => false,
            'label' => '商品名を入力',
            'empty_data' => null,
            'attr' => array(
                'maxlength' => 50,
            ),
        ));
        $builder->add('pageno', 'hidden', array(
        ));
        $builder->add('disp_number', 'product_list_max', array(
            'label' => '表示件数',
        ));
        $builder->add('orderby', 'product_list_order_by', array(
            'label' => '表示順',
        ));
        $builder->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
    }

    /**
     * Overdrive choice Category method
     * @param $Categories
     * @return array
     */
    private function getCategoryChoice($Categories)
    {
        $TmpCategories = array();

        foreach ($Categories as $Category) {
            $TmpCategories[] = $Category;
            if (count($Category->getChildren()) > 0) {
                $TmpCate = $this->getCategoryChoice($Category->getChildren());
                $TmpCategories = array_merge($TmpCategories, $TmpCate);
            }
        }

        return $TmpCategories;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'search_product';
    }
}
