import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Download from "@codegouvfr/react-dsfr/Download";
import React from "react";
import { createFileRoute } from "@tanstack/react-router";
import { fr } from "@codegouvfr/react-dsfr";

export const Route = createFileRoute("/agent/fdo/foire-aux-questions")({
  component: () => (
    <div>
      <Alert
        severity="info"
        title="Téléchargez le guide de déclaration d'erreur de bris de porte"
        description={
          <Download
            label="Guide utilisateur Déclaration de Bris de porte - erreur opérationnelle"
            details="PDF - 61,88 ko"
            linkProps={{
              href: "#",
            }}
          />
        }
      />
      <h1 className="fr-my-2w">Foire aux questions</h1>

      <div className={fr.cx("fr-accordions-group")}>
        <Accordion label="Qu’est-ce qu’une erreur opérationnelle ? ">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. In pulvinar
          diam vel viverra volutpat. Phasellus dapibus imperdiet ipsum, porta
          ornare dolor condimentum ut. Etiam volutpat dolor metus, id viverra
          lectus ullamcorper sed. Duis dictum turpis augue, id elementum augue
          blandit eu. Donec sed placerat neque. Mauris fermentum libero ut est
          sollicitudin, ut placerat sapien pulvinar. Donec placerat aliquet
          sapien eget porttitor. Mauris tincidunt, libero eget semper tempus,
          orci sapien tempor neque, et ultricies ligula metus sed erat. Aliquam
          lectus erat, dignissim non interdum non, fermentum sit amet ligula.
          Aliquam erat volutpat. Duis vitae rutrum magna. In eget sagittis arcu.
          Nulla sollicitudin luctus libero, quis pretium urna feugiat a. Donec
          ornare justo leo, non elementum odio egestas ac. Phasellus et velit
          pretium, lobortis quam id, finibus elit.
        </Accordion>
        <Accordion label="Qu’est-ce qu’une erreur opérationnelle ? ">
          Integer aliquam, tortor at congue tristique, eros mi feugiat libero,
          in feugiat ligula quam et tortor. In sit amet ante ut risus consequat
          auctor quis ac erat. Cras a pulvinar justo. Quisque convallis, felis
          quis feugiat dignissim, augue velit blandit risus, sed interdum risus
          mauris quis quam. Curabitur orci dolor, ornare ac ante quis, gravida
          dictum dui. Vivamus varius suscipit eleifend. Duis felis enim,
          convallis et tellus nec, placerat rutrum dolor. Sed lacus augue,
          vestibulum sed ipsum id, dignissim rhoncus lacus. Class aptent taciti
          sociosqu ad litora torquent per conubia nostra, per inceptos
          himenaeos. Quisque feugiat sapien nec erat efficitur, sed hendrerit
          orci accumsan. Cras placerat felis porta felis volutpat dignissim.
          Quisque pulvinar lectus quis nisi egestas vulputate. Nulla eleifend
          hendrerit feugiat. Nam commodo elit vehicula nisl elementum convallis.
          Curabitur quis nisi et enim commodo laoreet.
        </Accordion>
        <Accordion label="Qu’est-ce qu’une erreur opérationnelle ? ">
          Suspendisse lobortis sollicitudin ligula, non finibus ex molestie eu.
          Proin et sapien vitae neque posuere rhoncus. Nullam massa nisl,
          egestas at cursus sed, aliquet quis odio. Integer euismod mattis
          euismod. Maecenas felis ante, imperdiet tincidunt cursus sed, finibus
          mollis quam. Fusce pellentesque turpis egestas ipsum sodales aliquam.
          In malesuada vel eros in feugiat. Nunc rutrum metus justo, vel porta
          diam tincidunt nec. Pellentesque felis ligula, posuere vel euismod
          eget, vehicula eget velit. Nunc gravida eros id sapien pharetra
          viverra. Nunc in tortor ultricies orci vestibulum pretium. Vestibulum
          dictum ac purus sed suscipit. Phasellus nulla lectus, venenatis a diam
          a, pulvinar tristique nibh. Ut laoreet sed magna at aliquam. Praesent
          volutpat finibus luctus. Vivamus fringilla dui nec malesuada mollis.
        </Accordion>
        <Accordion label="Qu’est-ce qu’une erreur opérationnelle ? ">
          Etiam sodales imperdiet lorem, sit amet cursus lorem porttitor id.
          Nunc ut est elementum, interdum erat ut, commodo nibh. Duis sodales
          risus ac elit commodo, feugiat placerat arcu condimentum. Morbi
          posuere pellentesque ornare. Sed eu diam urna. Mauris ipsum augue,
          tristique a dictum sed, fermentum a urna. Nulla rutrum facilisis
          venenatis. Mauris sodales purus quis dui interdum volutpat vestibulum
          ut ipsum. Nulla quam mi, facilisis ut massa mollis, egestas commodo
          purus. Ut id odio vitae arcu faucibus aliquam in in ipsum. Nulla
          facilisi.
        </Accordion>
        <Accordion label="Qu’est-ce qu’une erreur opérationnelle ? ">
          Etiam faucibus libero lorem. Aliquam at risus tempus, pellentesque
          felis ac, viverra libero. Vivamus sed diam suscipit, luctus orci at,
          laoreet metus. Nulla quis convallis mauris. Maecenas in tortor id mi
          finibus tristique. Class aptent taciti sociosqu ad litora torquent per
          conubia nostra, per inceptos himenaeos. Etiam quis varius tortor. Nunc
          facilisis tortor id enim consectetur viverra. Etiam iaculis porttitor
          est, sit amet sagittis nisi feugiat vel. Vestibulum ante ipsum primis
          in faucibus orci luctus et ultrices posuere cubilia curae; Aliquam ac
          eros ligula. Praesent congue at felis vel pharetra. Etiam id risus
          finibus, porttitor odio ac, egestas felis. Cras ultricies metus est,
          et blandit tellus vestibulum vitae. Ut dui leo, blandit at erat sit
          amet, molestie viverra tellus. Phasellus efficitur pulvinar venenatis.
        </Accordion>
      </div>
    </div>
  ),
});
