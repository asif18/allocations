import { TestBed } from '@angular/core/testing';

import { ActiveUsersService } from './active-users.service';

describe('ActiveUsersService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ActiveUsersService = TestBed.get(ActiveUsersService);
    expect(service).toBeTruthy();
  });
});
