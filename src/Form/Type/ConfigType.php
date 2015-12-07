<?php
namespace Plugin\OmisePaymentGateway\Form\Type;

use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType {
	private $app;
	private $info;
	
	public function __construct(Application $app, array $info = null) {
		$this->app = $app;
		$this->info = $info;
	}
	
	/**
	 * 
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
        // 設定情報の初期化
        $this->init();

        $configService = $this->app['eccube.plugin.service.omise_config'];

        // フォーム内容の設定
        $builder
            ->add('pkey', 'text', array(
                'label' => 'Public key',
                'attr' => array(
                    'class' => '',
                	'maxlength' => 29,
                ),
                'data' => $this->info['pkey'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ Public keyが入力されていません。')),
                    new Assert\Length(array('max' => 29, 'maxMessage' => '※ Public keyは25桁（Test29桁）の文字列です。')),
                    new Assert\Length(array('min' => 25, 'minMessage' => '※ Public keyは25桁（Test29桁）の文字列です。')),
                ),
            ))
            ->add('skey', 'text', array(
                'label' => 'Secret key',
                'attr' => array(
                    'class' => '',
                	'maxlength' => 29,
                ),
                'data' => $this->info['skey'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ Secret keyが入力されていません。')),
                    new Assert\Length(array('max' => 29, 'maxMessage' => '※ ホスト番号は25桁（Test29桁）の数字です。')),
                    new Assert\Length(array('min' => 25, 'minMessage' => '※ ホスト番号は25桁（Test29桁）の数字です。')),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
	}
	
	public function getName() {
		return 'config';
	}
}
