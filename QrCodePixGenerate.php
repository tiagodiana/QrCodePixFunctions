<?php


class QrCodePixGenerate
{
    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * Chave Pix
     * @var string
     */
    private $pixKey;

    /**
     * Descrição do pagamento
     * @var string
     */
    private $description;

    /**
     * Nome do titular da conta
     * @var string
     */
    private $merchantName;

    /**
     * Cidade do titular da conta
     * @var string
     */
    private $merchantCity;

    /**
     * ID da transação pix
     * @var string
     */
    private $txid;

    /**
     * Valor da transação
     * @var string
     */
    private $amount;

    /**
     * Método responsavel por definir o valor do $pixKey
     * @param string $pixKey
     * @return QrCodePixGenerate
     */
    public function setPixKey(string $pixKey): QrCodePixGenerate
    {
        $this->pixKey = $pixKey;
        return $this;
    }

    /**
     * Método responsavel por definir o valor do $description
     * @param string $description
     * @return QrCodePixGenerate
     */
    public function setDescription(string $description): QrCodePixGenerate
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Método responsavel por definir o valor do $merchantName
     * @param string $merchantName
     * @return QrCodePixGenerate
     */
    public function setMerchantName(string $merchantName): QrCodePixGenerate
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    /**
     * Método responsavel por definir o valor do $merchantCity
     * @param string $merchantCity
     * @return QrCodePixGenerate
     */
    public function setMerchantCity(string $merchantCity): QrCodePixGenerate
    {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    /**
     * Método responsavel por definir o valor do $txid
     * @param string $txid
     * @return QrCodePixGenerate
     */
    public function setTxid(string $txid): QrCodePixGenerate
    {
        $this->txid = $txid;
        return $this;
    }

    /**
     * Método responsavel por definir o valor do $amount
     * @param float $amount
     * @return QrCodePixGenerate
     */
    public function setAmount(float $amount): QrCodePixGenerate
    {
        $this->amount = (string) number_format($amount, 2, '.', '');
        return $this;
    }

    /**
     * Método responsavel por gerar o codigo completo do payload Pix
     * @param string $id
     * @param string $value
     * @return string $id.$size.$value
     */
    private function getValue(string $id, string $value): string
    {
        $size = str_pad(mb_strlen($value), 2, '0', STR_PAD_LEFT);
        return $id.$size.$value;
    }

    /**
     * Método responsavel por retornar o valores completo da informação da conta
     * @return string
     */
    private function getMerchantAccountInformation(): string
    {
        //DOMINIO DO BANCO
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');

        //CHAVE DO PIX
        $key = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->pixKey);

        //DESCRIÇÃO DO PAGAMENTO
        $description = strlen($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->description) : '';

        //VALOR COMPLETO DA CONTA
        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui.$key.$description);
    }

    /**
     * Método responsavel por retornar os valores completos do campo adicional do pix (TXID)
     * @return string
     */
    private function getAdditionalDataFieldTemplate(): string
    {
        //TXID
        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txid);
        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload): string
    {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
    }

    /**
     * Método responsavel por gerar o codigo completo do payload Pix
     * @return string
     */
    public function getPayload()
    {
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR,  '01').
            $this->getMerchantAccountInformation().
            $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, '0000').
            $this->getValue(self::ID_TRANSACTION_CURRENCY,  '986').
            $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->amount).
            $this->getValue(self::ID_COUNTRY_CODE, 'BR').
            $this->getValue(self::ID_MERCHANT_NAME, $this->merchantName).
            $this->getValue(self::ID_MERCHANT_CITY, $this->merchantCity).
            $this->getAdditionalDataFieldTemplate();

        //RETORNA O PAYLOAD + CRC16
        return $payload.$this->getCRC16($payload);
    }
}