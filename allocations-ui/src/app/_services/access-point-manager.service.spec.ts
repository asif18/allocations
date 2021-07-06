import { TestBed } from '@angular/core/testing';

import { AccessPointManagerService } from './access-point-manager.service';

describe('AccessPointManagerService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: AccessPointManagerService = TestBed.get(AccessPointManagerService);
    expect(service).toBeTruthy();
  });
});
