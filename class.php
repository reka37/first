<?php

namespace frontend\controllers;
use backend\modules\catalog\models\SalesItems;
use backend\modules\catalog\models\OrdersItems;
use app\models\Auth;
use backend\models\Pages;
use backend\models\Settings;
use backend\modules\catalog\models\Brands;
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\OptionsCategory;
use backend\modules\catalog\models\OurStores;
use common\models\ArticleCategories;
use common\models\Articles;
use common\models\Banners;
use common\models\User;
use frontend\components\MainController;
use frontend\form\Order;
use common\models\Delivery;
use shadow\helpers\SArrayHelper;
use yii;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\Response;
use yii\widgets\LinkPager;
use frontend\controllers\ComplexController;

/**
 * Class SiteController
 *
 * @package frontend\controllers
 * @property \frontend\assets\AppAsset $AppAsset
 */
class SiteController extends MainController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'view' => '//site/error',
            ],
            'cart' => [
                'class' => 'frontend\components\CartAction',
            ],
            'send-form' => [
                'class' => 'frontend\components\SendFormAction',
                'forms' => [
                    'registration' => 'Registration',
                    'login' => 'Login',
                    'recovery' => 'Recovery',
                    'order' => 'Order',
                    'request' => 'SendRequest',
                    'request-window' => 'SendWindowRequest',
                    'fast_order' => 'FastOrder',
                    'callback' => 'CallbackSend',
                    'message' => 'MessageSend',
                    'subs' => 'Subscription',
                    'review_item' => 'ReviewItemSend',
                    'question_item' => 'SendQuestionItem',
                ],
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			]
        ];
    } 

    public function actionIndex()
    { 
        if ($code = Yii::$app->request->get('code')) {
            Yii::$app->session->set('invited_code', $code);
        }
		
		if ($city_currency = Yii::$app->request->get('currency')) {
            $currencies = $this->function_system->getCurrency_all();
            if (isset($currencies[$city_currency])) {
                Yii::$app->session->set('currency_select', $city_currency);
                $cookie = new Cookie(
                    [
                        'name' => 'currency_select',
                        'value' => $city_currency,
                        'expire' => time() + 604800,
                    ]
                );
                \Yii::$app->response->cookies->add($cookie);
				
				if (!Yii::$app->user->isGuest) {
					$user = User::findOne(\Yii::$app->user->identity->id);
					$user->rates_user_id = $city_currency;
					$user->save(false);
				}
                return $this->redirect(\Yii::$app->request->referrer);
            } else {
                return $this->redirect(['site/index']);
            }
        }
        $this->SeoSettings('main', 1, \Yii::t('main', 'Главная'));
        $items_hit = Items::find()
            ->where(['isVisible' => 1, 'isPublished' => 1, 'isHit' => 1, 'isDeleted' => 0])
            ->limit(6)
            ->all();
        $items_sale = Items::find()
            ->where(['isVisible' => 1, 'isPublished' => 1, 'isDeleted' => 0])
            ->andWhere(['or', ['is not', 'old_price', null], ['is not', 'discount', null]])
            ->all();
			
			if (\Yii::$app->user->isGuest) {
				$currency_select = \Yii::$app->session->get('currency_select', 1);
			} else {
				$currency_select = \Yii::$app->user->identity->rates_user_id;
			}
		  $items_new = Items::find()
            ->where(['`items`.isVisible' => 1, '`items`.isPublished' => 1, '`items`.isDeleted' => 0, '`items`.isNew' => 1])
            ->all();	

        $data = [
            'banners' => Banners::find()->andWhere(['isVisible' => 1])->orderBy(['sort' => SORT_ASC])->all(),
            'items_hit' => $items_hit,
            'items_sale' => $items_sale,
            'items_new' => $items_new,
        ];

        return $this->render('index', $data);
    }

    public function actionPage($id)
    {
        /**
         * @var $item Pages
         */
        $item = Pages::find()->andWhere(['isVisible' => 1, 'id' => $id])->one();
        if ($item) {
            $this->SeoSettings('page', $item->id, $item->name);
            $this->breadcrumbs[] = [
                'label' => $item->name,
                'url' => ['site/page', 'id' => $item->id],
            ];
            $data['item'] = $item;

            return $this->render('page', $data);
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function actionContacts()
    {
        $this->SeoSettings('module', 5, \Yii::t('main', 'Контакты'));
        $this->breadcrumbs[] = [
            'label' => \Yii::t('main', 'Контакты'),
            'url' => ['site/contacts'],
        ];
		
        return $this->render('contacts');
    }
    
      public function actionRegister()
    {
        $this->breadcrumbs[] = [
            'label' => \Yii::t('main', 'Регистрация'),
            'url' => ['site/register'],
        ];

        return $this->render('register');
    }

    public function actionOurStores($id)
    {
        $citys = $this->function_system->getCity_all();

        if (!isset($citys[$id])) {
            throw new BadRequestHttpException();
        }
        $city = $citys[$id];
        if (!count($city->ourStores)) {
            throw new BadRequestHttpException();
        }
        $this->SeoSettings('module', 6, \Yii::t('main', 'Пункты выдачи'));
        $this->breadcrumbs[] = [
            'label' => \Yii::t('main', 'Пункты выдачи'),
            'url' => ['site/our-stores', 'id' => $city->id],
        ];

        return $this->render(
            'our_stores', [
                'city' => $city,
            ]
        );
    }
}


