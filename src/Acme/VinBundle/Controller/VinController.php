<?php

namespace Acme\VinBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VinController extends Controller
{
    const TOYOTA           = 'toyota';
    const LEXUS            = 'lexus';
    const BMW              = 'bmw';
    const MINI             = 'mini';
    const ROLLSROYCE       = 'rollsroyce';
    const NISSAN           = 'nissan';
    const INFINITI         = 'infiniti';
    const KIA              = 'kia';
    const HYUNDAI          = 'hyundai';
    const AUDI             = 'audi';
    const VOLKSWAGEN       = 'volkswagen';
    const SEAT             = 'seat';
    const SKODA            = 'skoda';
    const MITSUBISHI       = 'mitsubishi';
    const SUBARU           = 'subaru';
    const SUZUKI           = 'suzuki';
    const MAZDA            = 'mazda';
    const HONDAEUROPE      = 'hondaeurope';
    const HONDA            = 'honda';
    const SAAB             = 'saab';
    const FIAT             = 'fiat';
    const FIATPROFESSIONAL = 'fiatprofessional';
    const ALFAROMEO        = 'alfaromeo';
    const LANCIA           = 'lancia';
    const ABARTH           = 'abarth';
    const MERCEDES         = 'mercedes';
    const SMART            = 'smart';
    const LANDROVER        = 'landrover';
    const CHEVROLETUSA     = 'chevroletusa';
    const CADILLAC         = 'cadillac';
    const PONTIAC          = 'pontiac';
    const BUICK            = 'buick';
    const HUMMER           = 'hummer';
    const SATURN           = 'saturn';
    const GMC              = 'gmc';
    const OLDSMOBILE       = 'oldsmobile';

    const MARKS = [
        self::TOYOTA,
        self::LEXUS,
        self::NISSAN,
        self::INFINITI,
        self::KIA,
        self::HYUNDAI,
        self::AUDI,
        self::VOLKSWAGEN,
        self::SEAT,
        self::SKODA,
        self::MITSUBISHI,
        self::SUBARU,
        self::SUZUKI,
        self::MAZDA,
        self::HONDAEUROPE,
        self::HONDA,
        self::SAAB,
        self::FIAT,
        self::FIATPROFESSIONAL,
        self::ALFAROMEO,
        self::LANCIA,
        self::ABARTH,
        self::MERCEDES,
        self::SMART,
        self::LANDROVER,
        self::CHEVROLETUSA,
        self::CADILLAC,
        self::PONTIAC,
        self::BUICK,
        self::HUMMER,
        self::SATURN,
        self::GMC,
        self::OLDSMOBILE,

        //группа БМВ во избежание дублирования выдачи должна ВСЕГДА находиться в конце этого массива
        self::MINI,
        self::ROLLSROYCE,
        self::BMW,
    ];
    const VAG   = [
        self::AUDI,
        self::VOLKSWAGEN,
        self::SEAT,
        self::SKODA,
    ];

    const NO_DATA          = 'no_data';
    const NOT_PAID_SERVICE = 'not_paid_service';

    /**
     * @param $vin
     * @param $domain
     *
     * @return JsonResponse
     */
    public function mainAction($vin, $domain)
    {
        $mark_in_VAG = false;
        foreach (self::MARKS as $mark) {
            if (!in_array($mark, self::VAG)) {
                $vinFinderResult = $this->get($mark . '.vin.model')->getVinFinderResult($vin, true);
                $mark_in_VAG     = false;
            } else {
                $vinFinderResult = $this->get($mark . '.vin.model')->getVinRegions($vin, true);
                $mark_in_VAG     = true;
            }
            if (!empty($vinFinderResult)) {
                break;
            }
        }

        if (empty($vinFinderResult)) {
            return new JsonResponse(self::NO_DATA);
        }

        $domainCheckCollection = $this->get('domain_info')->getDomainData($domain);
        $token                 = null;

        $markCode = !$mark_in_VAG ? $vinFinderResult['result']['marka'] : $vinFinderResult[array_keys($vinFinderResult)[0]]['result']['marka'];

        foreach ($domainCheckCollection as $item) {
            if (strcasecmp($item->code, $markCode) == 0 && $item->status != 0) {
                $token = $item->token;
            }
        }

        if (!$token) {
            return new JsonResponse(self::NOT_PAID_SERVICE);
        }

        if (!$mark_in_VAG) {
            $path   = $vinFinderResult['urlParams']['path'];
            $params = $vinFinderResult['urlParams']['params'];
            $url    = $this->generateUrl($path, array_merge($params, ['token' => $token, 'vin' => $vin]),
                UrlGeneratorInterface::ABSOLUTE_URL);

            $result = $vinFinderResult['result'];
            return new JsonResponse([array_merge($result, ['url' => $url])]);
        } else {
            foreach ($vinFinderResult as $index => $item) {
                $path     = $item['urlParams']['path'];
                $params   = $item['urlParams']['params'];
                $url      = $this->generateUrl($path, array_merge($params, ['token' => $token, 'vin' => $vin]),
                    UrlGeneratorInterface::ABSOLUTE_URL);
                $result[] = array_merge($item['result'], ['url' => $url]);
            }
            return new JsonResponse($result);
        }
    }
}
