/*
    Aerospace Decoder - Copyright (C) 2018 Bob Anderson (VK2GJ)

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*/

/*===========================================================================*/
/* Aerospace Decoder configuration definition and ChibiOS system includes.   */
/*===========================================================================*/

#include "pktconf.h"

/**
 * @file    dbguart.c
 * @brief   Serial channels for debug.
 *
 * @addtogroup IODevices
 * @{
 */

#if PKT_CFG_USE_SERIAL == TRUE
const SerialConfig debug_config = {
  115200,
  0,
  0,
  0
};

/* Declare UART aliases. */
BaseSequentialStream* diag_out = (BaseSequentialStream*) &SD3;
BaseSequentialStream* pkt_out = (BaseSequentialStream*) &SD4;

void pktSerialStart() {
  pktConfigSerialDiag();
  pktConfigSerialPkt();
  sdStart(&SD4, &debug_config);
  sdStart(&SD3, &debug_config);
}
#endif /* PKT_CFG_USE_SERIAL */

/** @} */
