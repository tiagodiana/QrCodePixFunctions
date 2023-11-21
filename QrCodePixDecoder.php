<?php

class QrCodePixDecoder
{
    public string $qrcode;

    public function __construct(string $qrcode)
    {
        $this->qrcode = $qrcode;
    }

    public function getPixKey(): string
    {
        $this->qrcode = strtolower($this->qrcode);
        $pixExplode = explode('br.gov.bcb.pix', $this->qrcode)[1];

        $keySize = substr($pixExplode, 2, 2);

        return  substr($pixExplode, 4, $keySize);
    }

    public function getPixValue(): string
    {
        //5303986 id, size, id from currency (CONST)
        $this->qrcode = strtolower($this->qrcode);
        $pixExplode = explode('5303986', $this->qrcode)[1];

        $keySize = substr($pixExplode, 2, 2);

        return  substr($pixExplode, 4, $keySize);
    }

    public function getPixClientName(): string
    {
        //5303986 id, size, id from currency (CONST)
        $this->qrcode = strtolower($this->qrcode);
        $pixExplode = explode('5303986', $this->qrcode)[1];

        $sizeInit = (int) substr($pixExplode, 2, 2) + 4;
        $pixExplode = substr($pixExplode, $sizeInit);
        $keySize = (int) substr($pixExplode, 8, 2);

        return  strtoupper(substr($pixExplode, 10, $keySize)
        );
    }

}
