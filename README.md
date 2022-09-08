# WC-Wallet-Cashback
Woo Wallet Cash Back plugin will add a 10% back to User Wallet on the basis of 100 day or 100 weeks plan.

## Plugin Logic

Customers who make an order on the website can get the full amount of cash paid (excluding discounts, payment from wallet, tax, shipping, etc) back as cashback over time. Cash back will be made in their Tera Wallet account

The 100 days plan applies to the 1st quantity of every item.

The 100 week plan applies to the rest of the quantity past the first qty purchased.
This applies to every item.

## Plugin Setting Page

1st qty / rest of qty to be able to be changed in settings: We can set the 100 day plan to instead be 50 days for example, or 30 days.

### Example A:

Customer buys 1 quantity of Product A for the first time:
$45 for product
$5 discount 
$10 wallet amount used
$4 tax
$3 shipping
= $37 paid
However, the customer receives this amount for cashback:
$45 - $5 discount - $10 wallet = $30  
So every 10 days the customer will receive 10% ($3) of the cash paid back into their wallet.

If the customer bought 1 qty of Product A for the 2nd time: 
Every 10 weeks the customer receives 10% ($3) cashback.

### Example B: 

If the customer buys 2 quantity of Product B for the first time:
$40 for products ($20 * 2)
$10 discount
$4 tax
Free shipping
= $34 paid
100 day plan for 1st qty: $20 - ( $10 disc / 2 qty ) = $15 
Every 10 days, they receive $1.50
100 week plan for 2nd qty: $20 - ( $10 disc / 2 qty ) = $15 
Every 10 weeks, they receive $1.50

### Example C: 
If the customer buys 3 quantity of Product C for the first time:
$60 for products ($20 * 3)
$10 discount
$2 wallet
$6 tax
Free shipping
= $54 paid
100 day plan for 1st qty: $20 - ( ( $10 disc + $2 wallet ) / 3 qty ) = $16 
Every 10 days, they receive $1.60
100 week plan for rest qty: 2 qty * ( $20 - ( $10 disc + $2 wallet / 3 qty ) ) = $32
Every 10 weeks, they receive $3.20

## Beginning of Cashback Plan

Customers begin receiving after the return period ends: the return period begins when the shipment is delivered. Return period will be 100 days. 

## Cashback Display

There should be a tab in the Wallet page where users can view past cashback transactions and pending cashback. 

