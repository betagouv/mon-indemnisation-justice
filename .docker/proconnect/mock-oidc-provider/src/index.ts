import { OAuth2Server } from 'oauth2-mock-server';

let server = new OAuth2Server();

// use JWKs generated here https://mkjwk.org/
await server.issuer.keys.add({
    "p": "9B6mboyeh7j8KBWFyL-Sxgta-RRcnxqm4Ey_1vYvCV_R9PgZhLzkIOLSoBqxqBn5m6spcuOTmLDBXrdmxvyVIuIuLJTMbg9MrO9H7GgbLYVMSumHFsk6NZFKlDGmA4HfEk0RKQwctIFNhCJnvE8NDQP0enyl3Ii32WjttM9gmtM",
    "kty": "RSA",
    "q": "8d4BwO1jgFK_DvWlwVMhxbhwX4IEuBaqe7HU0qGRCwAHqPe5_2jsBliP-Cyaqj4oeNFyNpVyBupGAy5md6YsicvFg2IOVwX0eQNGIvxIcFofTyc5q9eV3ppZxVVt01MVKHJ62269CAMp3pTOYgYzUhA9ltVy0V-g8xfcjZ3rycE",
    "d": "jsUelRMiqCRnXlYTOBqjBOqOmyjgMqFDXRlSyHbaVaRAjO_9-awCzD8kTKLEZdsqaaXbrVcAyqWF7up4YKs8Ogz5YPQ8RMhysC-Wg786m-Z4fsHlYHuQHP50bgApqYgA5KXZ_NEoOEQYasyAQuv5rBlTwueyDjRD0Ya4ED16NFhxZSplq-HPvDGh3sNtKsD_2na15AHyT8Mrbglax404n3dzGwR4itcQ4TGx-NV_yZ-LfbvFIvLA2tTijFmcrNdUjbBztLEyD9RlqVUxt0L64LB8Ti5WnX8Z7pk7gW_Cshs9-9WY_rOlH7G0wKCwtHc4J5iczotwG-tLBpsbV-fEAQ",
    "e": "AQAB",
    "use": "sig",
    "kid": "kREyd2R7x30J8jw3vVvnMCvqa3SgvreX2Py9fDfOhhs",
    "qi": "jd2JsBBXT7F8nSt0sQdARhd_T12unE-SzL1Ba-dW-xk8srRNFJoUL_DQ9Mh4ap70OhN03YfNfWoVhWfbLulDGJfTS-1jSpAwkNjQ37r0YopaNql5Uc6_e1PbHywwmOvzTmXoRfO6MV7GkPvoQI3VQfg_BVbpJbbMXTpJUOuMoTg",
    "dp": "4Ulb7M_HCZf01QUvxtjNGgmmthFNfON6t6r8q-Pb-rm8KGfCHRa5LYRGPs0DwQ_fpAKsqMNPFe2hNsjoh6DX_S5g0HGty7BA4aOda5WT50mJ4-Rp7-Ra3M6t1d-PxtLWy6vlt0zfPNHLeLLLqyQE9kr9FwDrjH97s587J87t51M",
    "alg": "RS256",
    "dq": "n521Zo7DJYJ2F_dBBk5IcWWAs2bomlxK8gqvurlpMOs6IuYpV0b0zFNR36QABe2zY9hFGYSSlcGxppPOh5OgvOc_V680a3XAmsPwW1JGs-6lzuTCCIfjnd4a9EGmktm3ktPKBFoWYd1EmmF1ufcBk1yoaIxAk7AaVZMfuE3Jt4E",
    "n": "5qSO7n5BswHdYseE6R4bj5v28vjaQr94Sn4BUudU1wycABq5VYftxvde7qC-rcKdPCCLdGFrsOHdf18bFCpi4RylRMsgaqZwh-o7ywdpICohc-yj-_efoZZl9B1t9oUJ0rVtg8flACDRf4A_GawTVeQIDbPnGTsg5qxo8qgsvVX9MbAuAHD3OOwe9zZRKsYuV6Z-3uxpOdVklY40rfaAYy6yf8BBDR_W0GCLhbEj8dmeVsqhIoss5ISdUvj4M7gKgkPdBxcwuDRPR2J2QSGMOg8MGTNJSnjkC6fYqTmGk1Ab_4gcO-1mAqq0XJP5nSOTHpmV_Cz3DbnSHmL7uBVkEw"
});
await server.issuer.keys.add({
    "kty": "EC",
    "d": "QVZNI6vv0NEshgrd8JzYm3JA3YomEnm8j9a_gBuh3G8",
    "use": "sig",
    "crv": "P-256",
    "kid": "Jh9D8jQqOfbNi4AM1r8-mKU_KOpeMbjGRduPyDXxVy4",
    "x": "SiYrczxd1TP6NXuDTNp3KqBsVKm7yEIBqCUp3_fiaS8",
    "y": "1OI5MJQ2JhMtODX8WLVzGP-5tRPU-131I58AD0yg2sM",
    "alg": "ES256"
});

server.issuer.url = "https://proconnect.anje-justice.test"
server.service.once('beforeAuthorizeRedirect', (authorizeRedirectUri, req) => {
  console.log(authorizeRedirectUri);
});

server.service.addListener('beforeUserinfo', (userInfoResponse, req) => {
  /*
  userInfoResponse.body = {
    sub: 'c1722a03-4172-4015-9f0d-d1995d4cbe5c',
    email: 'redacteur@test.fr',
    usual_name: "Chantal",
    given_name: "Redacteur",
    uid: '1234',
    idp_id: 'fce74da7-34d5-4b59-ba78-828a97d859cf'
  };
  */
  /*
  userInfoResponse.body = {
    sub: 'e316bde4-3d37-4ed8-9001-c40d9b07fe06',
    email: 'police@test.fr',
    usual_name: "Jerome",
    given_name: "Policier",
    uid: '567',
    idp_id: 'e2f13a1f-9f43-4def-a5ed-06dce37d00e6'
  };
  */
  userInfoResponse.body = {
    sub: 'eeb5c5d6-88eb-4d1c-b870-01bb26a84a0a',
    email: 'pierre.lemee@beta.gouv.fr',
    usual_name: "Pierre",
    given_name: "Lemée",
    uid: '7654',
    idp_id: '71144ab3-ee1a-4401-b7b3-79b44f7daeeb'
  };
});

// Start the server
await server.start(parseInt(process.env.PORT || "9998", 10), '0.0.0.0');
console.log('Issuer URL:', server.issuer.url);

// Stop the server
// await server.stop();
