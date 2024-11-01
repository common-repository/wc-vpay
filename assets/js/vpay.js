jQuery(function ($) {
  initForm();
  jQuery("#vpay-payment-button").click(function () {
    $("form#payment-form, form#order-review").submit();
  });

  function initForm() {
    let body = $("body");
    body.append(
      '<script id="vpayscript" async src="https://dropin-sandbox.vpay.africa/dropin/v1/initialise.js"></script>'
    );
    let barcodeDiv = $("#barcode-img");
    barcodeDiv.append(
      '<img class="wc-vpay-form-barcode" src="' +
        wc_vpay_params.barcode_img +
        '" alt="VPay Payment Barcode" />'
    );
    let form = $("form#payment-form, form#order-review");
    form.append(
      '<input type="hidden" name="vpay_txnref" value="' +
        wc_vpay_params.txnref +
        '"/>'
    );
  }
});

// const complete = require("../../includes/order-complete.php");

function openVPayPopup() {
  const {
    amount,
    currency,
    email,
    key,
    txnref,
    customer_service_channel,
    domain,
  } = wc_vpay_params;

  const params = {
    amount,
    currency,
    email,
    key,
    domain,
    transactionref: txnref,
    customer_service_channel,
    isWoocommerce: true,
    onSuccess: function (response) {
      jQuery(function ($) {
        $.ajax({
          url: "json-receive.php",
          type: "post",
          data: { payload: response },
          success: function (response) {
            //do whatever.
          },
        });
      });
    },
  };
  const { open, exit } = VPayDropin.create(params);
  open();
}
