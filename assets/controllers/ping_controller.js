import { Controller } from 'stimulus';

import ReactDOM from 'react-dom';
import React from 'react';
import BrisPorte from '../react/components/BrisPorte';

export default class extends Controller {
    connect() {
      ReactDOM.render(
          <BrisPorte />,
          this.element
      )
    }
}
