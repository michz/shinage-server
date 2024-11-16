<?php

namespace spec\App\Service;

use App\Service\HmacCalculator;
use App\Service\HmacCalculatorInterface;
use App\Service\UrlBuilder;
use App\Service\UrlBuilderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class HmacCalculatorSpec extends ObjectBehavior
{
    private const string SECRET = 'secret';

    public function let() {
        $this->beConstructedWith(self::SECRET);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(HmacCalculator::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(HmacCalculatorInterface::class);
    }

    public function it_can_calculate_an_hmac() {
        $expected = '48744e72539e445d7da0ce63abc315caf75ed9d9425140e06fcdc444c6b58634e9da980a50df0f243050ea23d23092fde5cc2eaf0d563e86430fc58dae04c211';
        $ts = '1731776411';
        $uid = 4;
        $oldPassword = '$2y$13$Tv8fdVnL7GquqhX4FtJuD.eLzsmsSKIQijdQ1cBZkV.8ByviLkgvK';
        $this
            ->calculate([
                'uid' => $uid,
                'ts' => $ts,
                'oldPassword' => $oldPassword,
            ])
            ->shouldReturn($expected);
    }

    public function it_can_verify_an_hmac_successfully() {
        $hmac = '48744e72539e445d7da0ce63abc315caf75ed9d9425140e06fcdc444c6b58634e9da980a50df0f243050ea23d23092fde5cc2eaf0d563e86430fc58dae04c211';
        $ts = '1731776411';
        $uid = 4;
        $oldPassword = '$2y$13$Tv8fdVnL7GquqhX4FtJuD.eLzsmsSKIQijdQ1cBZkV.8ByviLkgvK';
        $this
            ->verify(
                [
                    'uid' => $uid,
                    'ts' => $ts,
                    'oldPassword' => $oldPassword,
                ],
                $hmac)
            ->shouldReturn(true);
    }

    public function it_can_verify_an_hmac_reject_ts() {
        $hmac = '48744e72539e445d7da0ce63abc315caf75ed9d9425140e06fcdc444c6b58634e9da980a50df0f243050ea23d23092fde5cc2eaf0d563e86430fc58dae04c211';
        $ts = '1731776412';
        $uid = 4;
        $oldPassword = '$2y$13$Tv8fdVnL7GquqhX4FtJuD.eLzsmsSKIQijdQ1cBZkV.8ByviLkgvK';
        $this
            ->verify(
                [
                    'uid' => $uid,
                    'ts' => $ts,
                    'oldPassword' => $oldPassword,
                ],
                $hmac)
            ->shouldReturn(false);
    }

    public function it_can_verify_an_hmac_reject_uid() {
        $hmac = '48744e72539e445d7da0ce63abc315caf75ed9d9425140e06fcdc444c6b58634e9da980a50df0f243050ea23d23092fde5cc2eaf0d563e86430fc58dae04c211';
        $ts = '1731776411';
        $uid = 3;
        $oldPassword = '$2y$13$Tv8fdVnL7GquqhX4FtJuD.eLzsmsSKIQijdQ1cBZkV.8ByviLkgvK';
        $this
            ->verify(
                [
                    'uid' => $uid,
                    'ts' => $ts,
                    'oldPassword' => $oldPassword,
                ],
                $hmac)
            ->shouldReturn(false);
    }

    public function it_can_verify_an_hmac_reject_pwd() {
        $hmac = '48744e72539e445d7da0ce63abc315caf75ed9d9425140e06fcdc444c6b58634e9da980a50df0f243050ea23d23092fde5cc2eaf0d563e86430fc58dae04c211';
        $ts = '1731776411';
        $uid = 4;
        $oldPassword = '$3y$13$Tv8fdVnL7GquqhX4FtJuD.eLzsmsSKIQijdQ1cBZkV.8ByviLkgvK';
        $this
            ->verify(
                [
                    'uid' => $uid,
                    'ts' => $ts,
                    'oldPassword' => $oldPassword,
                ],
                $hmac)
            ->shouldReturn(false);
    }

    public function it_can_verify_an_hmac_reject_hmac() {
        $hmac = '38744e72539e445d7da0ce63abc315caf75ed9d9425140e06fcdc444c6b58634e9da980a50df0f243050ea23d23092fde5cc2eaf0d563e86430fc58dae04c211';
        $ts = '1731776411';
        $uid = 4;
        $oldPassword = '$2y$13$Tv8fdVnL7GquqhX4FtJuD.eLzsmsSKIQijdQ1cBZkV.8ByviLkgvK';
        $this
            ->verify(
                [
                    'uid' => $uid,
                    'ts' => $ts,
                    'oldPassword' => $oldPassword,
                ],
                $hmac)
            ->shouldReturn(false);
    }
}
