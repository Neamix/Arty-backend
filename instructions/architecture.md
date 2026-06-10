- This Project need to follow event architecture pattern 

To know how to do that you need to do the following decide first what is the main reason of the request as example lets say that the user want to register so the thing that the user need is to have 2 things the first one is the user data the secound is the workspace so first you will create the user second you will create the workspace but also the user need otp and need welcome on board so you have 2 things so you will use mail service (Create it if you didnt find it ) and send otp and board email but through even queue not direct 

- This Project need to be clean pattern 

(Policy if neeeded) Controller (Validate in request) -> Service -> Repository ->  Model 
