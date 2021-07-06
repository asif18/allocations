import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IpBindingComponent } from './ip-binding.component';

describe('IpBindingComponent', () => {
  let component: IpBindingComponent;
  let fixture: ComponentFixture<IpBindingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IpBindingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IpBindingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
